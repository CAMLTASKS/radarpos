const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const pino = require('pino');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

let sock = null;

// === Configuración de WhatsApp con Baileys ===
async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState('baileys_auth_info');
    
    sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
        logger: pino({ level: 'silent' })
    });

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            console.log('¡Escanea este código QR con tu WhatsApp para iniciar el Bot!');
            qrcode.generate(qr, { small: true });
        }

        if (connection === 'close') {
            const shouldReconnect = (lastDisconnect.error?.output?.statusCode !== DisconnectReason.loggedOut);
            console.log('Conexión cerrada. Reconectando...', shouldReconnect);
            if (shouldReconnect) {
                connectToWhatsApp();
            }
        } else if (connection === 'open') {
            console.log('✅ Cliente de WhatsApp (Baileys) listo y conectado.');
        }
    });

    sock.ev.on('creds.update', saveCreds);
}

connectToWhatsApp();

// === Configuración de WebSockets (Socket.io) ===
io.on('connection', (socket) => {
    console.log(`Cliente conectado a WS: ${socket.id}`);

    socket.on('join-order', (orderId) => {
        socket.join(`order-${orderId}`);
        console.log(`Cliente se unió a la sala: order-${orderId}`);
    });

    socket.on('disconnect', () => {
        console.log(`Cliente desconectado: ${socket.id}`);
    });
});

// === Endpoints ===

app.post('/api/send-whatsapp', async (req, res) => {
    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ error: 'Falta teléfono o mensaje' });
    }

    try {
        let cleanPhone = phone.replace(/\D/g, '');
        const formattedPhone = `${cleanPhone}@s.whatsapp.net`; // Formato para Baileys
        
        if (sock) {
            // Se quitó la verificación sock.onWhatsApp ya que a veces falla con números válidos o sin consultar.

            if (req.body.pdf_path) {
                const fs = require('fs');
                if (fs.existsSync(req.body.pdf_path)) {
                    await sock.sendMessage(formattedPhone, { 
                        document: fs.readFileSync(req.body.pdf_path), 
                        mimetype: 'application/pdf', 
                        fileName: 'Factura-Sunber.pdf',
                        caption: message
                    });
                } else {
                    await sock.sendMessage(formattedPhone, { text: message });
                }
            } else {
                await sock.sendMessage(formattedPhone, { text: message });
            }
            
            res.json({ success: true, message: 'Mensaje enviado' });
        } else {
            res.json({ success: false, error: 'WhatsApp no está listo' });
        }
    } catch (error) {
        console.error('Error enviando mensaje de WhatsApp:', error.message || error);
        res.json({ success: false, error: 'Fallo al enviar mensaje', details: error.message || error });
    }
});

app.post('/api/update-order-status', (req, res) => {
    const { orderId, status } = req.body;

    if (!orderId || !status) {
        return res.status(400).json({ error: 'Falta orderId o status' });
    }

    io.to(`order-${orderId}`).emit('order-status-changed', { orderId, status });
    console.log(`Estado de orden ${orderId} actualizado a ${status} vía WebSockets`);
    
    res.json({ success: true, message: 'Estado emitido por WebSockets' });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Microservicio Node.js corriendo en el puerto ${PORT}`);
});
