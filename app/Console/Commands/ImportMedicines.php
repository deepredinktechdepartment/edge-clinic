<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMedicines extends Command
{
    protected $signature   = 'import:medicines {file : Path to the CSV file}';
    protected $description = 'Import medicines data from CSV file (memory efficient)';

    private const CHUNK_SIZE = 500;

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            $this->error("Cannot open file.");
            return 1;
        }

        // Read and normalize headers — lowercase + spaces to underscores
        $headers = fgetcsv($handle);
        $headers = array_map(
            fn($h) => str_replace(' ', '_', strtolower(trim((string) $h))),
            $headers
        );

        // Debug: uncomment to verify headers if issues persist
        // $this->info("Headers: " . implode(', ', $headers));

        DB::disableQueryLog();

        $chunk      = [];
        $total      = 0;
        $chunkCount = 0;

        $this->info("Importing... (streaming mode)");

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) !== count($headers)) {
                continue;
            }

            $row     = array_combine($headers, $data);
            $chunk[] = $this->mapRow($row);
            $total++;

            if (count($chunk) >= self::CHUNK_SIZE) {
                Medicine::upsert($chunk, ['id'], array_keys($chunk[0]));
                $chunkCount++;
                $this->line("  Inserted " . ($chunkCount * self::CHUNK_SIZE) . " rows...");
                $chunk = [];
                gc_collect_cycles();
            }
        }

        if (! empty($chunk)) {
            Medicine::upsert($chunk, ['id'], array_keys($chunk[0]));
        }

        fclose($handle);

        $this->info("✅ Import complete! {$total} records processed.");
        return 0;
    }

    private function mapRow(array $row): array
    {
        return [
            'id'                        => (int) $this->val($row, 'id'),
            'name'                      => $this->val($row, 'name'),
            'price_inr'                 => $this->decimal($row, 'price'),
            'is_discontinued'           => $this->bool($row, 'is_discontinued'),
            'manufacturer_name'         => $this->val($row, 'manufacturer_name'),
            'type'                      => $this->val($row, 'type'),
            'pack_size_label'           => $this->val($row, 'pack_size_label'),
            'short_composition1'        => $this->val($row, 'short_composition1'),
            'short_composition2'        => $this->val($row, 'short_composition2'),
            'substitute0'               => $this->val($row, 'substitute0'),
            'substitute1'               => $this->val($row, 'substitute1'),
            'substitute2'               => $this->val($row, 'substitute2'),
            'substitute3'               => $this->val($row, 'substitute3'),
            'substitute4'               => $this->val($row, 'substitute4'),
            'consolidated_side_effects' => $this->val($row, 'consolidated_side_effects'),
            'use0'                      => $this->val($row, 'use0'),
            'use1'                      => $this->val($row, 'use1'),
            'use2'                      => $this->val($row, 'use2'),
            'use3'                      => $this->val($row, 'use3'),
            'use4'                      => $this->val($row, 'use4'),
            'chemical_class'            => $this->val($row, 'chemical_class'),
            'habit_forming'             => $this->bool($row, 'habit_forming'),
            'therapeutic_class'         => $this->val($row, 'therapeutic_class'),
            'action_class'              => $this->val($row, 'action_class'),
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }

    private function val(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        if ($value === null || trim((string) $value) === '' || strtolower(trim((string) $value)) === 'nan') {
            return null;
        }
        $value   = trim((string) $value);
        $encoded = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        $encoded = mb_convert_encoding($encoded, 'UTF-8', 'UTF-8');
        return $encoded ?: null;
    }

    private function decimal(array $row, string $key): ?float
    {
        $v = $this->val($row, $key);
        return $v !== null ? (float) $v : null;
    }

    private function bool(array $row, string $key): bool
    {
        $v = strtolower(trim((string) ($row[$key] ?? '')));
        return in_array($v, ['true', 'yes', '1'], true);
    }
}