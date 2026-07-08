<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda #{{ $order->id }} | Sunber</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }
        .receipt-wrapper {
            background: white;
            width: 320px;
            padding: 20px 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
        }
        .header { text-align: center; padding-bottom: 12px; border-bottom: 2px dashed #000; margin-bottom: 12px; }
        .header h2 { font-size: 22px; letter-spacing: 4px; }
        .header p { font-size: 12px; margin-top: 2px; }
        .info-block { padding-bottom: 10px; border-bottom: 1px dashed #ccc; margin-bottom: 10px; font-size: 13px; }
        .info-block .row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .label { color: #666; }
        .badge { font-weight: bold; text-transform: uppercase; }
        .items-table { width: 100%; font-size: 13px; margin-bottom: 10px; }
        .items-table .th-row { display: flex; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; }
        .item-row { display: flex; margin-bottom: 6px; }
        .col-qty { width: 28px; flex-shrink: 0; font-weight: bold; }
        .col-desc { flex: 1; padding-right: 6px; }
        .col-price { width: 65px; text-align: right; flex-shrink: 0; font-weight: bold; }
        .item-note { font-size: 11px; font-style: italic; color: #555; margin-top: 1px; }
        .item-extra { font-size: 11px; color: #333; }
        .totals { border-top: 1px dashed #000; padding-top: 10px; font-size: 13px; }
        .totals .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .totals .grand { font-size: 18px; font-weight: bold; border-top: 1px solid #000; padding-top: 6px; margin-top: 4px; }
        .footer { text-align: center; padding-top: 12px; border-top: 2px dashed #000; margin-top: 12px; font-size: 12px; line-height: 1.8; }
        .footer .thanks { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .print-btn { display: block; width: 100%; padding: 10px; background: #111; color: #fff; border: none; font-size: 14px; font-weight: bold; cursor: pointer; margin-top: 16px; border-radius: 4px; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-wrapper { box-shadow: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
<div class="receipt-wrapper">

    <div class="header">
        <h2>🍨 SUNBER</h2>
        <p>Heladería</p>
        <p>Pedido <strong>#{{ $order->id }}</strong></p>
        <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-block">
        <div class="row">
            <span class="label">Tipo:</span>
            <span class="badge">{{ $order->delivery_type === 'delivery' ? '🛵 Domicilio' : '🍽️ Mesa / Local' }}</span>
        </div>
        @if($order->customer_name)
        <div class="row">
            <span class="label">Cliente:</span>
            <span>{{ $order->customer_name }}</span>
        </div>
        @endif
        @if($order->customer_phone)
        <div class="row">
            <span class="label">Teléfono:</span>
            <span>{{ $order->customer_phone }}</span>
        </div>
        @endif
        @if($order->delivery_type === 'delivery' && $order->delivery_address)
        <div style="margin-top:6px;">
            <span class="label">📍 Dirección:</span>
            <div style="font-weight:bold; text-transform:uppercase; margin-top:2px;">{{ $order->delivery_address }}</div>
        </div>
        @endif
        @if($order->delivery_driver)
        <div class="row" style="margin-top:4px;">
            <span class="label">Repartidor:</span>
            <span>{{ $order->delivery_driver }}</span>
        </div>
        @endif
    </div>

    <div class="items-table">
        <div class="th-row">
            <span class="col-qty">#</span>
            <span class="col-desc">Producto</span>
            <span class="col-price">Total</span>
        </div>
        @foreach($order->items as $item)
        <div class="item-row">
            <span class="col-qty">{{ $item->quantity }}x</span>
            <div class="col-desc">
                <div>{{ $item->product->name }}</div>
                @if($item->variation)
                <div class="item-extra">↳ {{ $item->variation->name }}</div>
                @endif
                @if($item->notes)
                <div class="item-note">★ {{ $item->notes }}</div>
                @endif
            </div>
            <span class="col-price">${{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>

    <div class="totals">
        <div class="row">
            <span>Subtotal</span>
            <span>${{ number_format($order->total - $order->delivery_fee, 0, ',', '.') }}</span>
        </div>
        @if($order->delivery_type === 'delivery')
        <div class="row">
            <span>Domicilio</span>
            <span>${{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="row grand">
            <span>TOTAL</span>
            <span>${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="row" style="margin-top:8px; font-size:12px;">
            <span>Método de pago</span>
            <span>{{ $order->payment_method }}</span>
        </div>
    </div>

    <div class="footer">
        <p>¡Gracias por tu compra!</p>
        <p class="thanks">😋 Que lo disfrutes</p>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Imprimir Ticket</button>

</div>
</body>
</html>
