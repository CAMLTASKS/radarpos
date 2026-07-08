<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        $completedToday = \App\Models\Order::whereDate('created_at', today())->where('status', 'completed')->get();
        $todaySales = $completedToday->sum('total');
        $deliveredOrders = $completedToday->count();
        
        $activeOrders = \App\Models\Order::whereDate('created_at', today())->whereNotIn('status', ['completed', 'cancelled'])->count();
        $lowStockAlerts = \App\Models\Ingredient::where('stock', '<', 100)->count();

        $recentFeedback = \App\Models\Order::whereNotNull('rating')->latest('updated_at')->take(5)->get();
        
        // Calculate chart data (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $sales = \App\Models\Order::whereDate('created_at', $date)->where('status', 'completed')->sum('total');
            $chartData['labels'][] = $date->format('D');
            $chartData['data'][] = (float) $sales;
        }

        $averageOrderTime = 0;
        if ($deliveredOrders > 0) {
            $totalMinutes = 0;
            foreach ($completedToday as $o) {
                $totalMinutes += $o->created_at->diffInMinutes($o->updated_at);
            }
            $averageOrderTime = round($totalMinutes / $deliveredOrders);
        }

        return Inertia::render('Dashboard', [
            'todaySales' => (float) $todaySales,
            'activeOrders' => $activeOrders,
            'deliveredOrders' => $deliveredOrders,
            'lowStockAlerts' => $lowStockAlerts,
            'recentFeedback' => $recentFeedback,
            'chartData' => $chartData,
            'averageOrderTime' => $averageOrderTime,
        ]);
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/pos', function () {
    $catSetting = \App\Models\Setting::where('key_name', 'categories')->first();
    $categories = $catSetting ? array_filter(array_map('trim', explode(',', $catSetting->value))) : ['General'];
    if (empty($categories)) $categories = ['General'];

    $feeSetting = \App\Models\Setting::where('key_name', 'default_delivery_fee')->first();

    return Inertia::render('POS', [
        'products' => \App\Models\Product::with(['ingredients', 'variations'])->get(),
        'addons' => \App\Models\Product::where('is_addon', true)->get(),
        'categories' => $categories,
        'activePromotions' => \App\Models\Promotion::where('is_active', true)->get(),
        'defaultDeliveryFee' => $feeSetting ? (int)$feeSetting->value : 5000,
    ]);
})->middleware(['auth', 'verified'])->name('pos');

Route::get('/cash-register', function () {
    $currentSession = \App\Models\CashRegister::with('notes')->where('status', 'open')->latest()->first();
    
    $breakdown = [];
    $ordersCount = 0;
    if ($currentSession) {
        $orders = \App\Models\Order::where('created_at', '>=', $currentSession->opened_at)->where('status', 'completed')->get();
        $ordersCount = $orders->count();
        $breakdown = [
            'Efectivo' => $orders->where('payment_method', 'Efectivo')->sum('total'),
            'Nequi' => $orders->where('payment_method', 'Nequi')->sum('total'),
            'Daviplata' => $orders->where('payment_method', 'Daviplata')->sum('total'),
            'Tarjeta' => $orders->where('payment_method', 'Tarjeta')->sum('total'),
            'Total' => $orders->sum('total')
        ];
    }
    
    return Inertia::render('CashRegister', [
        'currentSession' => $currentSession,
        'breakdown' => $breakdown,
        'ordersCount' => $ordersCount
    ]);
})->middleware(['auth', 'verified'])->name('cash-register');

Route::get('/orders', function () {
    return Inertia::render('Orders', [
        'orders' => \App\Models\Order::with('items.product')->latest()->get()
    ]);
})->middleware(['auth', 'verified'])->name('orders');

// removed duplicate settings route

Route::get('/deliveries', function () {
    return Inertia::render('Deliveries', [
        'deliveries' => \App\Models\Order::with('items.product')
            ->whereIn('status', ['pending', 'preparing', 'ready', 'on_way'])
            ->latest()
            ->get()
    ]);
})->middleware(['auth', 'verified'])->name('deliveries');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    Route::get('/inventory', function () {
        return Inertia::render('Inventory', [
            'ingredients' => \App\Models\Ingredient::all()
        ]);
    })->name('inventory');

    Route::get('/products', function () {
        $catSetting = \App\Models\Setting::where('key_name', 'categories')->first();
        $categories = $catSetting ? array_filter(array_map('trim', explode(',', $catSetting->value))) : ['General'];
        if (empty($categories)) $categories = ['General'];

        return Inertia::render('Products', [
            'products' => \App\Models\Product::with(['ingredients', 'variations'])->get(),
            'ingredients' => \App\Models\Ingredient::all(),
            'categories' => $categories
        ]);
    })->name('products');

    Route::get('/customers', function () {
        return Inertia::render('Customers', [
            'customers' => \App\Models\Customer::orderBy('points', 'desc')->get()
        ]);
    })->name('customers');

    Route::get('/sales', function () {
        return Inertia::render('Reports', [
            'orders' => \App\Models\Order::with(['items.product'])->orderBy('created_at', 'desc')->get()
        ]);
    })->name('sales');

    Route::get('/comments', function () {
        return Inertia::render('Comments', [
            'orders' => \App\Models\Order::whereNotNull('rating')->latest('updated_at')->get()
        ]);
    })->name('comments');

    Route::get('/reports', function () {
        return Inertia::render('Reports', [
            'orders' => \App\Models\Order::with('items.product')->latest()->get()
        ]);
    })->name('reports');
});

Route::get('/tracking/{order}', function (\App\Models\Order $order) {
    return view('tracking', ['order' => $order]);
})->name('tracking');

Route::get('/orders/{order}/receipt', function (\App\Models\Order $order) {
    $order->load(['items.product', 'items.variation']);
    return view('receipt', ['order' => $order]);
})->name('orders.receipt');

Route::post('/api/orders/{order}/feedback', [\App\Http\Controllers\OrderController::class, 'submitFeedback'])->name('tracking.feedback');
Route::post('/api/tracking/{order}/confirm', [\App\Http\Controllers\OrderController::class, 'confirmDelivery'])->name('tracking.confirm');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::middleware(['admin'])->group(function () {
        // Users
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store']);
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update']);
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy']);

        // Settings & Drivers
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'storeSetting']);
        Route::put('/settings/{setting}', [\App\Http\Controllers\SettingController::class, 'updateSetting']);
        Route::delete('/settings/{setting}', [\App\Http\Controllers\SettingController::class, 'destroySetting']);
        
        Route::post('/drivers', [\App\Http\Controllers\SettingController::class, 'storeDriver']);
        Route::put('/drivers/{driver}', [\App\Http\Controllers\SettingController::class, 'updateDriver']);
        Route::delete('/drivers/{driver}', [\App\Http\Controllers\SettingController::class, 'destroyDriver']);

        Route::post('/settings/categories', [\App\Http\Controllers\SettingController::class, 'updateCategories']);
        
        Route::post('/api/products', [\App\Http\Controllers\ProductController::class, 'store']);
        Route::put('/api/products/{product}', [\App\Http\Controllers\ProductController::class, 'update']);
        Route::delete('/api/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy']);

        Route::post('/api/promotions', [\App\Http\Controllers\PromotionController::class, 'store']);
        Route::put('/api/promotions/{promotion}', [\App\Http\Controllers\PromotionController::class, 'update']);
        Route::delete('/api/promotions/{promotion}', [\App\Http\Controllers\PromotionController::class, 'destroy']);
        
        Route::post('/api/ingredients', [\App\Http\Controllers\IngredientController::class, 'store']);
        Route::put('/api/ingredients/{ingredient}', [\App\Http\Controllers\IngredientController::class, 'update']);
        Route::delete('/api/ingredients/{ingredient}', [\App\Http\Controllers\IngredientController::class, 'destroy']);
    });
    
    Route::get('/api/drivers', [\App\Http\Controllers\SettingController::class, 'getActiveDrivers']);
    
    // API Orders
    Route::post('/api/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::patch('/api/orders/{order}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus']);
    
    Route::get('/api/customers/{phone}', function ($phone) {
        return \App\Models\Customer::where('phone', $phone)->first();
    });

    Route::get('/api/customers/{id}/orders', function ($id) {
        return \App\Models\Order::where('customer_phone', \App\Models\Customer::find($id)->phone)
                                ->with('items.product', 'items.addons')
                                ->latest()
                                ->get();
    });
    
    Route::get('/api/cash-registers/status', [\App\Http\Controllers\CashRegisterController::class, 'status']);
    Route::post('/api/cash-registers/open', [\App\Http\Controllers\CashRegisterController::class, 'open']);
    Route::post('/api/cash-registers/close', [\App\Http\Controllers\CashRegisterController::class, 'close']);
    Route::post('/api/cash-registers/notes', [\App\Http\Controllers\CashRegisterController::class, 'addNote']);
    
    Route::get('/api/notifications', function () {
        $pendingCount = \App\Models\Order::where('status', 'pending')->count();
        $delayedCount = \App\Models\Order::whereIn('status', ['pending', 'preparing'])
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();
        $newCommentsCount = \App\Models\Order::whereNotNull('rating')
            ->whereDate('updated_at', today())
            ->count();

        return response()->json([
            'pending' => $pendingCount,
            'delayed' => $delayedCount,
            'comments' => $newCommentsCount
        ]);
    });
});

require __DIR__.'/auth.php';
