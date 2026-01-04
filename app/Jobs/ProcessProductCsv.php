<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use SplFileObject;
use App\Jobs\ProcessProductCsvChunk;

class ProcessProductCsv implements ShouldQueue
{
    use Queueable;

    public string $importId;
    public string $path;

    public function __construct(string $importId, string $path)
    {
        $this->importId = $importId;
        $this->path = $path;
    }

    public function handle()
    {
        $fullPath = Storage::path($this->path);
        $file = new SplFileObject($fullPath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $header = null;
        $chunkSize = 500;  // adjust
        $rows = [];

        foreach ($file as $row) {
            if ($file->key() === 0) {
                $header = $row;
                continue;
            }
            if (!$header) {
                break;
            }
            if (count($row) !== count($header)) {
                // skip or record invalid
                continue;
            }
            $rows[] = array_combine($header, $row);
            if (count($rows) >= $chunkSize) {
                ProcessProductCsvChunk::dispatch($this->importId, $rows);
                $rows = [];
            }
        }
        if (count($rows) > 0) {
            ProcessProductCsvChunk::dispatch($this->importId, $rows);
        }
    }
}