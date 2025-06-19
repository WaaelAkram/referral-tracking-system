const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const qrcode = require('qrcode-terminal');

console.log('Starting WhatsApp API Service...');

// --- WhatsApp Client Initialization ---
const client = new Client({
    authStrategy: new LocalAuth(), // Use local session storage
    puppeteer: {
        headless: true, // Run in headless mode
        args: ['--no-sandbox', '--disable-setuid-sandbox'] // Necessary for running in some environments (e.g., Docker)
    }
});

client.on('qr', (qr) => {
    console.log('[WHATSAPP API] QR code needs to be scanned.');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('[WHATSAPP API] Client is ready and connected!');
});

client.on('disconnected', (reason) => {
    console.log('[WHATSAPP API] Client was logged out', reason);
    // You might want to add logic here to exit the process or attempt to re-initialize
    process.exit(1); 
});

client.on('auth_failure', msg => {
    console.error('[WHATSAPP API] Authentication failure', msg);
    process.exit(1);
});

client.initialize();

// --- API Server Initialization (using Express) ---
const app = express();
app.use(express.json()); // Middleware to parse JSON bodies

const PORT = process.env.WHATSAPP_API_PORT || 3000;

// API endpoint to send a message
app.post('/send-message', async (req, res) => {
    const { to, message } = req.body;

    if (!to || !message) {
        return res.status(400).json({ status: 'error', message: 'Missing "to" or "message" in request body.' });
    }

    // --- THIS IS THE KEY CHANGE ---
    // The number is now expected to be pre-formatted by Laravel.
    // We just append the @c.us suffix.
    const chatId = `${to}@c.us`;
    // --- END OF CHANGE ---

    try {
        const msg = await client.sendMessage(chatId, message);
        console.log(`[WHATSAPP API] Message sent to ${to}`);
        res.status(200).json({ status: 'success', message: 'Message sent successfully.', messageId: msg.id._serialized });
    } catch (error) {
        console.error(`[WHATSAPP API] Failed to send message to ${to}:`, error);
        res.status(500).json({ status: 'error', message: 'Failed to send message.', error: error.toString() });
    }
});

app.listen(PORT, () => {
    console.log(`[WHATSAPP API] Server is listening on port ${PORT}`);
});

// Graceful shutdown
process.on('SIGINT', async () => {
    console.log('[WHATSAPP API] Shutting down...');
    await client.destroy();
    process.exit(0);
});