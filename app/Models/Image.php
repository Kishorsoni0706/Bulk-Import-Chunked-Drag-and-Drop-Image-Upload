<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'upload_id', 'filename', 'disk', 'path', 'variants'
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    public function getOriginalUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getVariantUrlsAttribute()
    {
        $urls = [];
        foreach ($this->variants as $size => $relPath) {
            $urls[$size] = Storage::disk($this->disk)->url($relPath);
        }
        return $urls;
    }
}