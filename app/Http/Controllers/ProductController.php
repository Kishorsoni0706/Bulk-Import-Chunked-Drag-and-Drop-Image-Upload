<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('primaryImage')->paginate(20);
        $out = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->primaryImage ? [
                    'original' => $product->primaryImage->original_url,
                    'variants' => $product->primaryImage->variant_urls,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $out,
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
            ],
        ]);
    }
}
