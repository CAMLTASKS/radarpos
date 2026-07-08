<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra #{{ $order->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #d97706; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        .info { margin-bottom: 20px; font-size: 14px; }
        .info table { w-full }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; font-size: 14px; }
        th { background-color: #f9fafb; font-weight: bold; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #888; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; background: #e5e7eb; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Sunber ERP 🍦</div>
        <div class="subtitle">Comprobante de Venta</div>
    </div>

    <div class="info">
        <table style="border:none; margin-bottom:0;">
            <tr>
                <td style="border:none; padding:0;"><strong>Pedido N°:</strong> #{{ $order->id }}</td>
                <td style="border:none; padding:0; text-align:right;"><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td style="border:none; padding:0; padding-top:10px;"><strong>Cliente:</strong> {{ $order->customer_name ?: 'Mostrador' }}</td>
                <td style="border:none; padding:0; text-align:right; padding-top:10px;"><strong>Tipo:</strong> {{ $order->delivery_type == 'delivery' ? 'Domicilio' : 'Mesa/Local' }}</td>
            </tr>
            @if($order->delivery_type == 'delivery')
            <tr>
                <td colspan="2" style="border:none; padding:0; padding-top:10px;"><strong>Dirección:</strong> {{ $order->delivery_address }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product->name }}
                    @if($item->variations)
                        <br><span style="font-size:11px; color:#666;">Variación: {{ $item->variations }}</span>
                    @endif
                    @if($item->addons)
                        <br><span style="font-size:11px; color:#666;">Extras: {{ $item->addons }}</span>
                    @endif
                    @if($item->notes)
                        <br><span style="font-size:11px; color:#d97706;">Nota: {{ $item->notes }}</span>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAL A PAGAR:</td>
                <td style="text-align: right;">${{ number_format($order->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>¡Gracias por tu compra en Sunber!</p>
        <p>Este documento es un comprobante de tu pedido generado automáticamente.</p>
    </div>
</body>
</html>
