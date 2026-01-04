<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Upload;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as ImageTool;
use App\Jobs\LinkImageToProductIfFound;

class MergeAndProcessImage implements ShouldQueue
{
    use Queueable;

    public Upload $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function handle()
    {
        $upload = $this->upload;
        if ($upload->status !== 'uploading') {
            // Already merged or failed; skip
            return;
        }

        \Chunky::merge($upload->upload_uuid, function ($mergedPath) use ($upload) {
            $disk = $upload->disk;
            $rel = 'uploads/images/' . $upload->upload_uuid . '/' . basename($mergedPath);
            Storage::disk($disk)->putFileAs(dirname($rel), new \Illuminate\Http\File($mergedPath), basename($rel));

            $content = Storage::disk($disk)->get($rel);
            $computed = hash('sha256', $content);
            if ($upload->checksum !== $computed) {
                $upload->status = 'failed';
                $upload->save();
                return;
            }

            $img = Image::create([
                'upload_id' => $upload->id,
                'filename' => basename($rel),
                'disk' => $disk,
                'path' => $rel,
                'variants' => json_encode([]),
            ]);

            $sizes = [256, 512, 1024];
            $variants = [];
            $origFull = Storage::disk($disk)->path($rel);

            foreach ($sizes as $size) {
                $imgTool = ImageTool::make($origFull);
                $imgTool->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $variantName = pathinfo($img->filename, PATHINFO_FILENAME) . "_{$size}." . pathinfo($img->filename, PATHINFO_EXTENSION);
                $variantRel = dirname($rel) . '/variants/' . $variantName;
                Storage::disk($disk)->put($variantRel, (string) $imgTool->encode());
                $variants[$size] = $variantRel;
            }

            $img->variants = $variants;
            $img->save();

            $upload->status = 'merged';
            $upload->path = $rel;
            $upload->save();

            // Dispatch linking job
            LinkImageToProductIfFound::dispatch($upload->upload_uuid);
        });
    }
}