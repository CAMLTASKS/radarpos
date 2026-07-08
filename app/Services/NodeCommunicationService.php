<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodeCommunicationService
{
    protected $nodeUrl;

    public function __construct()
    {
        $this->nodeUrl = env('NODE_SERVER_URL', 'http://localhost:3000');
    }

    /**
     * Send a WhatsApp message through the Node service
     */
    public function sendWhatsAppMessage($phone, $message, $pdfPath = null)
    {
        try {
            $payload = [
                'phone' => $phone,
                'message' => $message,
            ];
            if ($pdfPath) {
                $payload['pdf_path'] = $pdfPath;
            }
            $response = Http::post("{$this->nodeUrl}/api/send-whatsapp", $payload);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error communicating with Node for WhatsApp: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Emit an order status change through WebSockets
     */
    public function emitOrderStatusChange($orderId, $status)
    {
        try {
            $response = Http::post("{$this->nodeUrl}/api/update-order-status", [
                'orderId' => $orderId,
                'status' => $status,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error communicating with Node for WebSockets: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
