<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\Upload;
use App\Models\Product;

class LinkImageToProductIfFound implements ShouldQueue
{
    use Queueable;

    protected string $uploadUuid;

    public function __construct(string $uploadUuid)
    {
        $this->uploadUuid = $uploadUuid;
    }

    public function handle()
    {
        $upload = Upload::where('upload_uuid', $this->uploadUuid)->with('images')->first();
        if (! $upload || $upload->status !== 'merged') {
            return;
        }

        $img = $upload->images()->latest()->first();
        if (! $img) {
            return;
        }

        $products = Product::where('image_upload_uuid', $this->uploadUuid)->get();
        foreach ($products as $product) {
            if ($product->primary_image_id !== $img->id) {
                $product->primary_image_id = $img->id;
                $product->save();
            }
        }
    }
}