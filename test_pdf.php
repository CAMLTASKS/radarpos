<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $order = \App\Models\Order::first();
    if (!$order) {
        echo "No order found\n";
        exit;
    }
    $order->load('items.product');
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['order' => $order]);
    $pdf->save(storage_path('app/public/invoices/test.pdf'));
    echo "PDF saved successfully\n";
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
}
