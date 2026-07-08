<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:ingredients',
            'unit' => 'required|string',
            'stock' => 'required|numeric',
            'cost' => 'required|numeric', // Valor Real (Costo)
        ]);

        Ingredient::create($request->all());

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $request->validate([
            'name' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|numeric',
            'cost' => 'required|numeric',
        ]);

        $ingredient->update($request->only('name', 'unit', 'stock', 'cost'));

        return response()->json(['success' => true]);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete(); // Soft delete
        return response()->json(['success' => true]);
    }
}
