<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Jobs\MergeAndProcessImage;

class ImageUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'upload_uuid' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'chunk' => 'required|file',
            'filename' => 'nullable|string',
        ]);

        $upload = Upload::firstOrCreate(
            ['upload_uuid' => $request->upload_uuid],
            ['filename' => $request->filename ?? $request->upload_uuid, 'status' => 'uploading']
        );

        $chunk = \Chunky::addChunk(
            $request->file('chunk'),
            $request->chunk_index,
            $upload->upload_uuid
        );
        return $chunk->toResponse();
    }

    public function completeUpload(Request $request)
    {
        $request->validate([
            'upload_uuid' => 'required|string',
            'checksum' => 'required|string',
        ]);

        $upload = Upload::where('upload_uuid', $request->upload_uuid)->firstOrFail();

        if ($upload->status === 'merged') {
            return response()->json(['message' => 'Already merged']);
        }

        $upload->checksum = $request->checksum;
        $upload->save();

        MergeAndProcessImage::dispatch($upload);

        return response()->json(['message' => 'Merge initiated']);
    }
}