<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Throwable;

class ImportLegacyDatabaseDump extends Command
{
    protected $signature = 'import:legacy-dump
        {file : Path to the SQL dump file}
        {--exclude=medicines,icd10_codes,migrations : Comma-separated tables to preserve}
        {--temp-db= : Temporary database name to use while loading the dump}
        {--keep-temp-db : Keep the temporary database after import}
        {--dry-run : Show what would be imported without changing current data}';

    protected $description = 'Import legacy production dump data into the current schema while preserving selected core tables.';

    public function handle(): int
    {
        $filePath = (string) $this->argument('file');

        if (! is_file($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $excludedTables = $this->excludedTables();
        $tempDatabase = $this->option('temp-db') ?: 'edge_clinic_legacy_import_' . now()->format('Ymd_His');

        try {
            $serverPdo = $this->makeServerPdo();
            $currentDatabase = (string) DB::getDatabaseName();
            $currentTables = $this->getTableNames($currentDatabase);

            if (in_array($tempDatabase, $currentTables, true)) {
                throw new RuntimeException("Temporary database name conflicts with an existing table: {$tempDatabase}");
            }

            $this->loadDumpIntoTemporaryDatabase($serverPdo, $tempDatabase, $filePath);
            $sourceTables = $this->getTableNames($tempDatabase);

            $truncateTables = array_values(array_diff($currentTables, $excludedTables));
            sort($truncateTables);

            $copyPlan = [];
            foreach ($truncateTables as $table) {
                if (! in_array($table, $sourceTables, true)) {
                    $copyPlan[$table] = [];
                    continue;
                }

                $destinationColumns = $this->getColumnNames($currentDatabase, $table);
                $sourceColumns = $this->getColumnNames($tempDatabase, $table);
                $copyPlan[$table] = array_values(array_intersect($destinationColumns, $sourceColumns));
            }

            $this->displayPlan($currentDatabase, $tempDatabase, $excludedTables, $truncateTables, $copyPlan);

            if ($this->option('dry-run')) {
                $this->info('Dry run complete. No data was changed.');
                $this->dropTemporaryDatabaseIfNeeded($serverPdo, $tempDatabase);
                return self::SUCCESS;
            }

            $this->truncateAndImport($currentDatabase, $tempDatabase, $truncateTables, $copyPlan);
            $this->backfillAppointmentsFromPayments();

            $this->info('Legacy production data import completed successfully.');

            if ($this->option('keep-temp-db')) {
                $this->warn("Temporary database kept: {$tempDatabase}");
            } else {
                $this->dropTemporaryDatabaseIfNeeded($serverPdo, $tempDatabase);
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function excludedTables(): array
    {
        $exclude = array_filter(array_map(
            static fn (string $table): string => trim($table),
            explode(',', (string) $this->option('exclude'))
        ));

        return array_values(array_unique($exclude));
    }

    private function makeServerPdo(): PDO
    {
        $config = DB::connection()->getConfig();

        $charset = $config['charset'] ?? 'utf8mb4';
        if (! empty($config['unix_socket'])) {
            $dsn = "mysql:unix_socket={$config['unix_socket']};charset={$charset}";
        } else {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 3306;
            $dsn = "mysql:host={$host};port={$port};charset={$charset}";
        }

        $pdo = new PDO(
            $dsn,
            $config['username'] ?? null,
            $config['password'] ?? null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return $pdo;
    }

    private function loadDumpIntoTemporaryDatabase(PDO $serverPdo, string $tempDatabase, string $filePath): void
    {
        $this->line("Loading dump into temporary database `{$tempDatabase}`...");

        $serverPdo->exec('DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($tempDatabase));
        $serverPdo->exec('CREATE DATABASE ' . $this->quoteIdentifier($tempDatabase) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $serverPdo->exec('USE ' . $this->quoteIdentifier($tempDatabase));

        foreach ($this->readSqlStatements($filePath) as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '' || $trimmed === ';') {
                continue;
            }

            $serverPdo->exec($trimmed);
        }
    }

    private function truncateAndImport(string $currentDatabase, string $tempDatabase, array $truncateTables, array $copyPlan): void
    {
        $this->line("Refreshing current database `{$currentDatabase}`...");

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($truncateTables as $table) {
                DB::statement('TRUNCATE TABLE ' . $this->quoteIdentifier($table));
            }

            foreach ($truncateTables as $table) {
                $columns = $copyPlan[$table] ?? [];
                if ($columns === []) {
                    continue;
                }

                $columnList = implode(', ', array_map([$this, 'quoteIdentifier'], $columns));
                $sql = sprintf(
                    'INSERT INTO %s.%s (%s) SELECT %s FROM %s.%s',
                    $this->quoteIdentifier($currentDatabase),
                    $this->quoteIdentifier($table),
                    $columnList,
                    $columnList,
                    $this->quoteIdentifier($tempDatabase),
                    $this->quoteIdentifier($table)
                );

                DB::statement($sql);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function backfillAppointmentsFromPayments(): void
    {
        $paymentColumns = $this->getColumnNames((string) DB::getDatabaseName(), 'payments');
        $appointmentColumns = $this->getColumnNames((string) DB::getDatabaseName(), 'appointments');

        if ($paymentColumns === [] || $appointmentColumns === []) {
            return;
        }

        if (! in_array('appointment_status', $paymentColumns, true) || ! in_array('appointment_status', $appointmentColumns, true)) {
            return;
        }

        $this->line('Backfilling missing appointments from payments...');

        DB::statement(<<<'SQL'
INSERT INTO `appointments` (
    `id`,
    `appointment_no`,
    `doctor_id`,
    `patient_id`,
    `date`,
    `time_slot`,
    `fee`,
    `payment_id`,
    `payment_status`,
    `appointment_status`,
    `payment_method`,
    `payment_request`,
    `payment_response`,
    `currency`,
    `payment_date`,
    `created_at`,
    `updated_at`
)
SELECT
    p.`id`,
    COALESCE(
        (
            SELECT asl.`appointment_no`
            FROM `appointment_status_logs` asl
            WHERE asl.`appointment_id` = p.`id`
                AND asl.`appointment_no` IS NOT NULL
                AND asl.`appointment_no` <> ''
                AND CHAR_LENGTH(asl.`appointment_no`) <= 20
            ORDER BY asl.`id` ASC
            LIMIT 1
        ),
        CONCAT('APTLEGACY', LPAD(p.`id`, 11, '0'))
    ) AS `appointment_no`,
    COALESCE(p.`doctor_id`, 0) AS `doctor_id`,
    COALESCE(p.`patient_id`, 0) AS `patient_id`,
    CASE
        WHEN CHAR_LENGTH(COALESCE(p.`aptDate`, '')) = 8 THEN STR_TO_DATE(p.`aptDate`, '%Y%m%d')
        WHEN CHAR_LENGTH(COALESCE(p.`aptDate`, '')) = 10 THEN STR_TO_DATE(p.`aptDate`, '%Y-%m-%d')
        ELSE DATE(COALESCE(p.`created_at`, NOW()))
    END AS `date`,
    NULLIF(p.`aptTime`, '') AS `time_slot`,
    COALESCE(p.`amount`, 0) AS `fee`,
    p.`payment_id`,
    CASE
        WHEN p.`status` = 'Authorized' THEN 'success'
        WHEN LOWER(COALESCE(p.`status`, '')) IN ('failed', 'failure') THEN 'failed'
        ELSE 'initiated'
    END AS `payment_status`,
    COALESCE(NULLIF(p.`appointment_status`, ''), 'Scheduled') AS `appointment_status`,
    NULLIF(p.`payment_mode`, '') AS `payment_method`,
    NULLIF(p.`response`, '') AS `payment_request`,
    NULLIF(p.`mocdoc_response`, '') AS `payment_response`,
    COALESCE(NULLIF(p.`currency`, ''), 'INR') AS `currency`,
    CASE
        WHEN p.`status` = 'Authorized' THEN COALESCE(p.`updated_at`, p.`created_at`, NOW())
        ELSE NULL
    END AS `payment_date`,
    COALESCE(p.`created_at`, NOW()) AS `created_at`,
    COALESCE(p.`updated_at`, p.`created_at`, NOW()) AS `updated_at`
FROM `payments` p
LEFT JOIN `appointments` a
    ON a.`id` = p.`id`
    OR (a.`payment_id` IS NOT NULL AND a.`payment_id` = p.`payment_id`)
WHERE p.`type` = 'appointment'
    AND a.`id` IS NULL
    AND (
        p.`payment_id` IS NOT NULL
        OR p.`mocdoc_apptkey` IS NOT NULL
        OR p.`aptDate` IS NOT NULL
        OR p.`aptTime` IS NOT NULL
    )
SQL);
    }

    private function displayPlan(string $currentDatabase, string $tempDatabase, array $excludedTables, array $truncateTables, array $copyPlan): void
    {
        $this->info("Current database: {$currentDatabase}");
        $this->info("Temporary database: {$tempDatabase}");
        $this->info('Preserved tables: ' . implode(', ', $excludedTables));
        $this->info('Tables to truncate: ' . implode(', ', $truncateTables));

        foreach ($truncateTables as $table) {
            $columns = $copyPlan[$table] ?? [];
            if ($columns === []) {
                $this->line(" - {$table}: truncate only (no source table in dump)");
                continue;
            }

            $this->line(" - {$table}: import " . count($columns) . ' columns');
        }
    }

    private function getTableNames(string $databaseName): array
    {
        $rows = DB::select(
            'SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ? AND table_type = ? ORDER BY TABLE_NAME',
            [$databaseName, 'BASE TABLE']
        );

        return array_map(static fn ($row) => $row->TABLE_NAME, $rows);
    }

    private function getColumnNames(string $databaseName, string $tableName): array
    {
        $rows = DB::select(
            'SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position',
            [$databaseName, $tableName]
        );

        return array_map(static fn ($row) => $row->COLUMN_NAME, $rows);
    }

    private function dropTemporaryDatabaseIfNeeded(PDO $serverPdo, string $tempDatabase): void
    {
        if ($this->option('keep-temp-db')) {
            return;
        }

        $serverPdo->exec('DROP DATABASE IF EXISTS ' . $this->quoteIdentifier($tempDatabase));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function readSqlStatements(string $filePath): \Generator
    {
        $handle = fopen($filePath, 'rb');
        if (! $handle) {
            throw new RuntimeException("Unable to open SQL dump: {$filePath}");
        }

        $statement = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $inLineComment = false;
        $inBlockComment = false;
        $escapeNext = false;
        $previousChar = '';

        try {
            while (($char = fgetc($handle)) !== false) {
                if ($inLineComment) {
                    if ($char === "\n") {
                        $inLineComment = false;
                    }
                    $previousChar = $char;
                    continue;
                }

                if ($inBlockComment) {
                    if ($previousChar === '*' && $char === '/') {
                        $inBlockComment = false;
                        $char = '';
                    }
                    $previousChar = $char;
                    continue;
                }

                if (! $inSingleQuote && ! $inDoubleQuote && ! $inBacktick) {
                    if ($previousChar === '-' && $char === '-') {
                        $statement = substr($statement, 0, -1);
                        $inLineComment = true;
                        $previousChar = $char;
                        continue;
                    }

                    if ($char === '#') {
                        $inLineComment = true;
                        $previousChar = $char;
                        continue;
                    }

                    if ($previousChar === '/' && $char === '*') {
                        $statement = substr($statement, 0, -1);
                        $inBlockComment = true;
                        $previousChar = $char;
                        continue;
                    }
                }

                if ($char !== '') {
                    $statement .= $char;
                }

                if ($inSingleQuote || $inDoubleQuote) {
                    if ($escapeNext) {
                        $escapeNext = false;
                    } elseif ($char === '\\') {
                        $escapeNext = true;
                    } elseif ($inSingleQuote && $char === "'") {
                        $inSingleQuote = false;
                    } elseif ($inDoubleQuote && $char === '"') {
                        $inDoubleQuote = false;
                    }
                } elseif ($inBacktick) {
                    if ($char === '`') {
                        $inBacktick = false;
                    }
                } else {
                    if ($char === "'") {
                        $inSingleQuote = true;
                    } elseif ($char === '"') {
                        $inDoubleQuote = true;
                    } elseif ($char === '`') {
                        $inBacktick = true;
                    } elseif ($char === ';') {
                        yield $statement;
                        $statement = '';
                    }
                }

                $previousChar = $char;
            }

            $trimmed = trim($statement);
            if ($trimmed !== '') {
                yield $statement;
            }
        } finally {
            fclose($handle);
        }
    }
}
