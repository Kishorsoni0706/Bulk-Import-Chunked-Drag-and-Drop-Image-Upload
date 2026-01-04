<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ProcessProductCsv;
use App\Services\ImportSummary;

class ProductImportController extends Controller
{
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        $file = $request->file('csv_file');
        $path = $file->store('imports');
        $importId = Str::uuid()->toString();

        ProcessProductCsv::dispatch($importId, $path);

        return response()->json([
            'import_id' => $importId,
            'message' => 'CSV upload accepted.',
        ]);
    }

    public function importSummary($importId)
    {
        $data = ImportSummary::getFinalSummary($importId);
        return response()->json($data);
    }
}


