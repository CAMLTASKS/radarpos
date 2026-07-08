<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Addon;
use App\Models\Customer;
use App\Services\NodeCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request, NodeCommunicationService $nodeService)
    {
        $request->validate([
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'delivery_type' => 'required|in:local,delivery',
            'delivery_fee' => 'required|numeric',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*.id' => 'nullable|integer',
            'items.*.addons.*.name' => 'required_with:items.*.addons|string',
            'items.*.addons.*.price' => 'required_with:items.*.addons|numeric',
            'items.*.notes' => 'nullable|string',
            'payment_method' => 'required|string',
            'promotion_id' => 'nullable|exists:promotions,id',
            'total' => 'nullable|numeric',
            'payment_method' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $nodeService) {
            $total = $request->delivery_fee; // Start total with delivery fee
            
            // Handle Customer Logic
            $customerId = null;
            if ($request->customer_phone) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer_phone],
                    ['name' => $request->customer_name ?? 'Cliente Frecuente']
                );
                $customerId = $customer->id;
                // Add points: 1 point per order
                $customer->increment('orders_count');
                $customer->increment('points', 10); // 10 points per order as an example
            }

            // Ensure there is an open cash register
            $activeRegister = \App\Models\CashRegister::where('status', 'open')->first();
            if (!$activeRegister) {
                return response()->json(['error' => 'No hay una caja abierta.'], 400);
            }

            // Create Order
            $order = Order::create([
                'cash_register_id' => $activeRegister->id,
                'customer_id' => $customerId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_type' => $request->delivery_type,
                'delivery_address' => $request->delivery_address ?? null,
                'delivery_driver' => $request->delivery_driver ?? null,
                'delivery_fee' => $request->delivery_fee,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'promotion_id' => $request->promotion_id ?? null,
                'total' => 0, // Will update
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::with('ingredients')->find($itemData['product_id']);
                $quantity = $itemData['quantity'];
                
                $unitPrice = $product->price;

                if (!empty($itemData['variation_id'])) {
                    $variation = ProductVariation::find($itemData['variation_id']);
                    $unitPrice += $variation->price_modifier;
                }

                $addonsCost = 0;
                $addonsToAttach = [];
                if (!empty($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addonObj) {
                        $addonsCost += floatval($addonObj['price']);
                        $addonsToAttach[] = $addonObj;
                    }
                }

                $unitPrice += $addonsCost;
                $itemTotal = $unitPrice * $quantity;
                $total += $itemTotal;

                // Create OrderItem
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variation_id' => $itemData['variation_id'] ?? null,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Attach Addons
                foreach ($addonsToAttach as $addonObj) {
                    DB::table('order_item_addons')->insert([
                        'order_item_id' => $orderItem->id,
                        'addon_id' => $addonObj['id'] ?? null,
                        'custom_name' => $addonObj['id'] ? null : $addonObj['name'],
                        'quantity' => $quantity,
                        'price' => $addonObj['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Reduce Ingredient Stock
                foreach ($product->ingredients as $ingredient) {
                    $usedAmount = $ingredient->pivot->quantity * $quantity;
                    $ingredient->decrement('stock', $usedAmount);
                }
            }

            // Aplicar descuento por promoción si existe (solo sobre subtotal)
            if ($request->promotion_id) {
                $promo = \App\Models\Promotion::find($request->promotion_id);
                if ($promo && $promo->is_active) {
                    if ($promo->type === 'percent') {
                        $total -= ($total * $promo->value / 100);
                    } else if ($promo->type === 'fixed') {
                        $total -= $promo->value;
                    }
                }
            }

            $order->update(['total' => max(0, $total)]);

            // If phone provided, send WhatsApp
            if ($order->customer_phone) {
                $trackingUrl = url("/tracking/{$order->id}");
                
                $message = "==============================\n";
                $message .= "     🍦 HELADERÍA SUNBER 🍦   \n";
                $message .= "==============================\n";
                $message .= "RECIBO DE COMPRA: #{$order->id}\n";
                $message .= "CLIENTE: " . strtoupper($order->customer_name) . "\n";
                $message .= "------------------------------\n";
                
                $subtotal = 0;
                foreach ($request->items as $itemData) {
                    $prod = Product::find($itemData['product_id']);
                    $qty = $itemData['quantity'];
                    
                    $unitPrice = $prod->price;
                    if (!empty($itemData['variation_id'])) {
                        $var = ProductVariation::find($itemData['variation_id']);
                        $unitPrice += $var->price_modifier;
                    }
                    if (!empty($itemData['addons'])) {
                        foreach ($itemData['addons'] as $addonObj) {
                            $unitPrice += floatval($addonObj['price']);
                        }
                    }
                    
                    $itemTotal = $unitPrice * $qty;
                    $subtotal += $itemTotal;
                    
                    $message .= "{$qty}x {$prod->name} ....... $" . number_format($itemTotal, 0) . "\n";
                    if (!empty($itemData['notes'])) {
                        $message .= "   (Nota: {$itemData['notes']})\n";
                    }
                }
                
                $message .= "------------------------------\n";
                $message .= "SUBTOTAL: $" . number_format($subtotal, 0) . "\n";
                if ($request->delivery_type === 'delivery') {
                    $message .= "DOMICILIO: $" . number_format($request->delivery_fee, 0) . "\n";
                }
                $message .= "==============================\n";
                $message .= "*TOTAL A PAGAR: $" . number_format($total, 0) . "*\n";
                $message .= "==============================\n\n";
                $message .= "🍨 *¡Gracias por elegir Sunber - Heladería Café!* 🍨\n\n";
                $message .= "Síguenos en nuestras redes sociales:\n";
                $message .= "📸 Instagram: @sunber_ice_sogamoso\n";
                $message .= "📘 Facebook: Sunber ice Sogamoso\n\n";
                $message .= "Sigue el estado de tu pedido en tiempo real aquí:\n{$trackingUrl}";

                // Generate PDF Invoice
                $order->load('items.product');
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['order' => $order]);
                $pdfFileName = "invoice_{$order->id}_" . time() . ".pdf";
                $pdfStoragePath = storage_path("app/public/invoices/{$pdfFileName}");
                
                if (!file_exists(storage_path('app/public/invoices'))) {
                    mkdir(storage_path('app/public/invoices'), 0755, true);
                }
                $pdf->save($pdfStoragePath);

                $nodeService->sendWhatsAppMessage($order->customer_phone, $message, $pdfStoragePath);
            }

            return response()->json(['success' => true, 'order' => $order]);
        });
    }

    public function updateStatus(Request $request, Order $order, NodeCommunicationService $nodeService)
    {
        $request->validate([
            'status' => 'required|string|in:pending,preparing,ready,on_way,completed,cancelled,returned',
            'delivery_time' => 'nullable|string', // For auto response
            'cancellation_reason' => 'nullable|string'
        ]);
        
        $updateData = ['status' => $request->status];
        if ($request->has('cancellation_reason')) {
            $updateData['cancellation_reason'] = $request->cancellation_reason;
        }

        $order->update($updateData);

        // Emit WebSockets update
        $nodeService->emitOrderStatusChange($order->id, $order->status);

        // Advanced WhatsApp Auto-responses
        if ($order->customer_phone) {
            $msg = "";
            $trackingUrl = url('/tracking/' . $order->id);
            
            if ($order->status === 'preparing') {
                $msg = "Tu pedido #{$order->id} está en *preparación en cocina* 👨‍🍳. ¡Pronto estará listo!";
            } else if ($order->status === 'on_way') {
                $time = $request->delivery_time ?? 'unos minutos';
                $msg = "El domiciliario va en *camino* con tu pedido #{$order->id} 🚚. Tiempo estimado: *{$time}*.";
            } else if ($order->status === 'ready' && $order->delivery_type === 'local') {
                $msg = "¡Tu pedido #{$order->id} está *listo* para recoger en la barra! 🍨\n\nPor favor, califica tu experiencia aquí:\n{$trackingUrl}";
            } else if ($order->status === 'ready' && $order->delivery_type === 'delivery') {
                $msg = "¡Tu pedido #{$order->id} está *listo* y a punto de salir hacia tu dirección! 🚚\n\nCuando lo recibas, no olvides calificar tu experiencia aquí:\n{$trackingUrl}";
            } else if ($order->status === 'completed' && $order->delivery_type === 'delivery') {
                $msg = "¡El domiciliario ha llegado con tu pedido #{$order->id}! Disfrútalo. 😋";
            } else if ($order->status === 'completed' && $order->delivery_type === 'local') {
                $msg = "¡Tu pedido #{$order->id} ha sido entregado! Gracias por preferirnos. 🍨";
            }

            if ($msg !== "") {
                $nodeService->sendWhatsAppMessage($order->customer_phone, $msg);
            }
        }

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function confirmDelivery(Order $order, NodeCommunicationService $nodeService)
    {
        if (!$order->customer_confirmed_at && $order->status !== 'completed') {
            $order->update(['customer_confirmed_at' => now()]);
            // Still emit a tracking update so the UI updates
            $nodeService->emitOrderStatusChange($order->id, 'customer_confirmed');
            
            if ($order->customer_phone) {
                $msg = "¡Hemos registrado que ya recibiste tu pedido #{$order->id}! Gracias por preferirnos. 😋";
                $nodeService->sendWhatsAppMessage($order->customer_phone, $msg);
            }
        }
        
        return response()->json(['success' => true, 'order' => $order]);
    }

    public function submitFeedback(Request $request, Order $order)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback_comments' => 'nullable|string'
        ]);

        $order->update([
            'rating' => $request->rating,
            'feedback_comments' => $request->feedback_comments
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback saved successfully.']);
    }
}
