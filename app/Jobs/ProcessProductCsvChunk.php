<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use App\Services\ImportSummary;
use App\Models\Product;

class ProcessProductCsvChunk implements ShouldQueue
{
    use Queueable;

    public string $importId;
    public array $rows;

    public function __construct(string $importId, array $rows)
    {
        $this->importId = $importId;
        $this->rows = $rows;
    }

    public function handle()
    {
        $summary = [
            'total' => 0,
            'inserted' => 0,
            'updated' => 0,
            'invalid' => 0,
            'duplicates' => 0,
        ];

        $seen = [];

        foreach ($this->rows as $row) {
            $summary['total']++;

            if (! isset($row['sku']) || ! isset($row['name'])) {
                $summary['invalid']++;
                continue;
            }
            $sku = trim($row['sku']);
            if ($sku === '') {
                $summary['invalid']++;
                continue;
            }
            if (isset($seen[$sku])) {
                $summary['duplicates']++;
                continue;
            }
            $seen[$sku] = true;

            $data = [
                'sku' => $sku,
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'price' => isset($row['price']) ? floatval($row['price']) : null,
                'updated_at' => now(),
            ];
            if (! empty($row['image_upload_uuid'])) {
                $data['image_upload_uuid'] = $row['image_upload_uuid'];
            }

            $existing = Product::where('sku', $sku)->first();
            if ($existing) {
                $existing->fill($data);
                $existing->save();
                $summary['updated']++;
            } else {
                $data['created_at'] = now();
                Product::create($data);
                $summary['inserted']++;
            }
        }

        ImportSummary::recordChunkResult($this->importId, $summary);
    }
}