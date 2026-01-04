<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'description', 'price', 'primary_image_id', 'image_upload_uuid'
    ];

    public function primaryImage()
    {
        return $this->belongsTo(Image::class, 'primary_image_id');
    }
}

