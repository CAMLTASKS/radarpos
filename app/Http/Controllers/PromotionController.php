<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        return response()->json(Promotion::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:percentage,fixed,free_product,free_addon',
            'value' => 'nullable|numeric',
            'free_product_id' => 'nullable|exists:products,id',
            'is_active' => 'boolean'
        ]);

        Promotion::create($request->all());

        return redirect()->back()->with('success', 'Promoción creada.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:percentage,fixed,free_product,free_addon',
            'value' => 'nullable|numeric',
            'free_product_id' => 'nullable|exists:products,id',
            'is_active' => 'boolean'
        ]);

        $promotion->update($request->all());

        return redirect()->back()->with('success', 'Promoción actualizada.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(['success' => true]);
    }
}
