<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        $drivers = DeliveryDriver::all();
        $promotions = \App\Models\Promotion::all();
        $users = \App\Models\User::all();
        $roles = \Spatie\Permission\Models\Role::all();
        
        return Inertia::render('Settings', [
            'settings' => $settings,
            'drivers' => $drivers,
            'promotions' => \App\Models\Promotion::with('freeProduct')->get(),
            'products' => \App\Models\Product::select('id', 'name', 'price')->orderBy('name')->get(),
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function storeSetting(Request $request)
    {
        $request->validate([
            'key_name' => 'required|string|unique:settings',
            'value' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        Setting::create($request->all());
        return redirect()->back()->with('success', 'Parámetro guardado.');
    }

    public function updateSetting(Request $request, Setting $setting)
    {
        $request->validate([
            'key_name' => 'required|string|unique:settings,key_name,'.$setting->id,
            'value' => 'nullable|string',
        ]);
        $setting->update($request->all());
        return redirect()->back()->with('success', 'Parámetro actualizado.');
    }
    
    public function destroySetting(Setting $setting)
    {
        $setting->delete();
        return redirect()->back()->with('success', 'Parámetro eliminado.');
    }

    // Driver Methods
    public function storeDriver(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        DeliveryDriver::create($request->all());
        return redirect()->back()->with('success', 'Repartidor agregado.');
    }

    public function updateDriver(Request $request, DeliveryDriver $driver)
    {
        $request->validate([
            'name' => 'required|string',
            'is_active' => 'boolean'
        ]);
        $driver->update($request->all());
        return redirect()->back()->with('success', 'Repartidor actualizado.');
    }

    public function destroyDriver(DeliveryDriver $driver)
    {
        $driver->delete();
        return redirect()->back()->with('success', 'Repartidor eliminado.');
    }

    public function getActiveDrivers()
    {
        return DeliveryDriver::where('is_active', true)->get();
    }

    public function updateCategories(Request $request)
    {
        $request->validate(['categories' => 'array']);
        $val = implode(',', $request->categories);
        Setting::updateOrCreate(['key_name' => 'categories'], ['value' => $val, 'description' => 'Categorías de productos']);
        return redirect()->back()->with('success', 'Categorías actualizadas.');
    }
}
