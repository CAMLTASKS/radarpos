<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'image' => 'nullable|string',
            'is_addon' => 'nullable|boolean',
            'ingredients' => 'nullable|array',
            'variations' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'category' => $request->category,
                'is_addon' => $request->is_addon ?? false,
                'image' => $request->image ?? '🍨',
            ]);

            if (!empty($request->ingredients)) {
                $syncData = [];
                foreach ($request->ingredients as $ing) {
                    $syncData[$ing['id']] = ['quantity' => $ing['quantity']];
                }
                $product->ingredients()->sync($syncData);
            }

            if (!empty($request->variations)) {
                foreach ($request->variations as $var) {
                    $product->variations()->create([
                        'name' => $var['name'],
                        'price_modifier' => $var['price_modifier'],
                    ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'image' => 'nullable|string',
            'is_addon' => 'nullable|boolean',
            'ingredients' => 'nullable|array',
            'variations' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $product) {
            $product->update([
                'name' => $request->name,
                'price' => $request->price,
                'category' => $request->category,
                'is_addon' => $request->is_addon ?? false,
                'image' => $request->image ?? '🍨',
            ]);

            if (!empty($request->ingredients)) {
                $syncData = [];
                foreach ($request->ingredients as $ing) {
                    $syncData[$ing['id']] = ['quantity' => $ing['quantity']];
                }
                $product->ingredients()->sync($syncData);
            } else {
                $product->ingredients()->detach();
            }

            // Actualizar variaciones: borramos y volvemos a crear por simplicidad
            $product->variations()->delete();
            if (!empty($request->variations)) {
                foreach ($request->variations as $var) {
                    $product->variations()->create([
                        'name' => $var['name'],
                        'price_modifier' => $var['price_modifier'],
                    ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroy(Product $product)
    {
        $product->delete(); // Soft delete
        return response()->json(['success' => true]);
    }
}
