<?php

namespace App\Console\Commands;

use App\Models\Icd10Code;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportIcd10Codes extends Command
{
    protected $signature   = 'import:icd10 {file : Path to the CSV file}';
    protected $description = 'Import ICD-10 codes from CSV (no headers)';

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

        DB::disableQueryLog();

        $chunk      = [];
        $total      = 0;
        $chunkCount = 0;

        $this->info("Importing ICD-10 codes... (streaming mode)");

        while (($data = fgetcsv($handle, 0, "\t")) !== false) { // try tab-delimited first
            // fallback: if only 1 column detected, try comma
            if (count($data) === 1) {
                $data = str_getcsv($data[0], ',');
            }

            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            // Pad to 5 columns in case some rows have missing trailing fields
            $data = array_pad($data, 5, null);

            $chunk[] = [
                'parent_code'       => $this->clean($data[0]),
                'sub_code'          => $this->clean($data[1]),
                'full_code'         => $this->clean($data[2]),
                'short_description' => $this->clean($data[3]),
                'long_description'  => $this->clean($data[4]),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $total++;

            if (count($chunk) >= self::CHUNK_SIZE) {
                Icd10Code::upsert($chunk, ['full_code'], [
                    'parent_code', 'sub_code', 'short_description', 'long_description', 'updated_at'
                ]);
                $chunkCount++;
                $this->line("  Inserted " . ($chunkCount * self::CHUNK_SIZE) . " rows...");
                $chunk = [];
                gc_collect_cycles();
            }
        }

        // Insert remaining rows
        if (! empty($chunk)) {
            Icd10Code::upsert($chunk, ['full_code'], [
                'parent_code', 'sub_code', 'short_description', 'long_description', 'updated_at'
            ]);
        }

        fclose($handle);

        $this->info("✅ Import complete! {$total} ICD-10 records processed.");
        return 0;
    }

    private function clean(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = mb_convert_encoding(trim($value), 'UTF-8', 'Windows-1252');
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8') ?: null;
    }
}