<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    public function status(Request $request)
    {
        $user = Auth::user() ?? \App\Models\User::first();
        
        $activeRegister = CashRegister::where('status', 'open')
            ->orderBy('opened_at', 'desc')
            ->first();

        return response()->json([
            'is_open' => $activeRegister !== null,
            'register' => $activeRegister
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0'
        ]);

        $user = Auth::user() ?? \App\Models\User::first();

        // Check if there's already an open register
        $activeRegister = CashRegister::where('status', 'open')->first();
        
        if ($activeRegister) {
            return response()->json(['error' => 'Ya existe una caja abierta.'], 400);
        }

        $register = CashRegister::create([
            'user_id' => $user->id,
            'opening_amount' => $request->opening_amount,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return response()->json(['success' => true, 'register' => $register]);
    }

    public function close(Request $request)
    {
        $request->validate([
            'closing_amount' => 'required|numeric|min:0'
        ]);

        $activeRegister = CashRegister::where('status', 'open')->first();
        
        if (!$activeRegister) {
            return response()->json(['error' => 'No hay ninguna caja abierta.'], 400);
        }

        $activeRegister->update([
            'closing_amount' => $request->closing_amount,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json(['success' => true, 'register' => $activeRegister]);
    }

    public function addNote(Request $request)
    {
        $request->validate([
            'note' => 'required|string',
            'type' => 'required|in:ingreso,retiro,observacion',
            'amount' => 'nullable|numeric'
        ]);

        $activeRegister = CashRegister::where('status', 'open')->latest()->first();
        if (!$activeRegister) {
            return response()->json(['error' => 'No hay caja abierta'], 400);
        }

        $note = \App\Models\CashRegisterNote::create([
            'cash_register_id' => $activeRegister->id,
            'note' => $request->note,
            'type' => $request->type,
            'amount' => $request->amount ?: 0,
        ]);

        return response()->json(['success' => true, 'note' => $note]);
    }
}
