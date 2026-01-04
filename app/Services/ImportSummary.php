<?php

namespace App\Services;

use App\Models\ImportSummary as ImportSummaryModel;

class ImportSummary
{
    public static function recordChunkResult(string $importId, array $summary)
    {
        $record = ImportSummaryModel::firstOrCreate(
            ['import_id' => $importId],
            ['data' => json_encode([
                'total' => 0, 'inserted' => 0, 'updated' => 0, 'invalid' => 0, 'duplicates' => 0
            ])]
        );
        $current = json_decode($record->data, true);
        foreach ($summary as $key => $val) {
            $current[$key] = ($current[$key] ?? 0) + $val;
        }
        $record->data = json_encode($current);
        $record->save();
    }

    public static function getFinalSummary(string $importId): array
    {
        $record = ImportSummaryModel::where('import_id', $importId)->first();
        return $record ? json_decode($record->data, true) : [];
    }
}
