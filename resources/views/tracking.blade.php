<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sigue tu Pedido | Sunber</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F4F6; }
        .progress-bar-fill { transition: width 1s cubic-bezier(0.4, 0, 0.2, 1); }
        #sound-overlay {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        /* Delivery themed styles */
        .delivery-hero {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .delivery-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
            position: relative;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        .step-circle.active {
            background: #fbbf24;
            box-shadow: 0 0 0 4px rgba(251,191,36,0.2);
        }
        .step-circle.done {
            background: #22c55e;
        }
        .step-label {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-align: center;
            line-height: 1.2;
        }
        .step-label.active { color: #f59e0b; }
        .step-label.done { color: #16a34a; }

        /* === DELIVERY ROAD ANIMATION === */
        .road-scene {
            position: relative;
            height: 120px;
            background: linear-gradient(180deg, #93c5fd 0%, #bfdbfe 40%, #d1fae5 40%, #6ee7b7 45%, #4ade80 45%, #4ade80 55%, #6ee7b7 55%, #d1fae5 60%, #f3f4f6 60%);
            border-radius: 16px;
            overflow: hidden;
            display: none;
        }
        .road-scene.visible { display: block; }
        .road-surface {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 44px;
            background: #374151;
            border-radius: 0 0 16px 16px;
        }
        .road-line {
            position: absolute;
            bottom: 18px;
            height: 4px;
            width: 60px;
            background: #fbbf24;
            border-radius: 2px;
            animation: road-scroll 1.2s linear infinite;
        }
        .road-line:nth-child(1) { left: 10%; animation-delay: 0s; }
        .road-line:nth-child(2) { left: 30%; animation-delay: -0.4s; }
        .road-line:nth-child(3) { left: 50%; animation-delay: -0.8s; }
        .road-line:nth-child(4) { left: 70%; animation-delay: -1.2s; }
        .road-line:nth-child(5) { left: 90%; animation-delay: -0.2s; }
        @keyframes road-scroll {
            0% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(-120px); opacity: 0.3; }
        }
        .moto-rider {
            position: absolute;
            font-size: 40px;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%) scaleX(-1);
            animation: moto-bounce 0.4s ease-in-out infinite alternate, moto-wobble 2s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            z-index: 10;
        }
        @keyframes moto-bounce {
            0% { bottom: 32px; }
            100% { bottom: 36px; }
        }
        @keyframes moto-wobble {
            0%, 100% { transform: translateX(-50%) scaleX(-1) rotate(-1deg); }
            50% { transform: translateX(-50%) scaleX(-1) rotate(1deg); }
        }
        .exhaust {
            position: absolute;
            bottom: 46px;
            left: 38%;
            z-index: 5;
        }
        .exhaust-puff {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(156,163,175,0.6);
            animation: exhaust-float 1s ease-out infinite;
            margin-right: -2px;
        }
        .exhaust-puff:nth-child(2) { animation-delay: 0.3s; width: 6px; height: 6px; }
        .exhaust-puff:nth-child(3) { animation-delay: 0.6s; width: 4px; height: 4px; }
        @keyframes exhaust-float {
            0% { transform: translate(0,0); opacity: 0.8; }
            100% { transform: translate(-30px,-20px); opacity: 0; }
        }
        .building {
            position: absolute;
            bottom: 44px;
            font-size: 28px;
            animation: building-scroll 4s linear infinite;
        }
        .building:nth-child(1) { right: -40px; animation-delay: 0s; }
        .building:nth-child(2) { right: -40px; animation-delay: -2s; font-size: 22px; bottom: 48px; }
        @keyframes building-scroll {
            0% { transform: translateX(100px); }
            100% { transform: translateX(-500px); }
        }
        .sun {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 28px;
            animation: sun-pulse 3s ease-in-out infinite;
        }
        @keyframes sun-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .cloud {
            position: absolute;
            top: 8px;
            font-size: 20px;
            animation: cloud-drift 8s linear infinite;
            opacity: 0.8;
        }
        .cloud:nth-child(1) { left: 10%; animation-delay: 0s; }
        .cloud:nth-child(2) { left: 50%; animation-delay: -4s; font-size: 14px; }
        @keyframes cloud-drift {
            0% { transform: translateX(0); }
            100% { transform: translateX(-200px); opacity: 0; }
        }

        /* Confirm button pulse */
        @keyframes confirm-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
            50% { box-shadow: 0 0 0 12px rgba(34,197,94,0); }
        }
        .confirm-btn {
            animation: confirm-pulse 2s infinite;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start py-8 px-4" id="body">
    
    <!-- Modal for Sound Activation -->
    <div id="sound-overlay" class="fixed inset-0 bg-gray-900/80 z-[100] flex flex-col items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl transform transition-transform hover:scale-105">
            <div class="text-6xl mb-4">🔊</div>
            <h2 class="text-2xl font-black text-gray-900 mb-2">Sigue tu pedido en vivo</h2>
            <p class="text-gray-500 mb-6 text-sm font-bold">Activa el sonido para recibir notificaciones importantes sobre tu orden.</p>
            <button id="activate-sound-btn" class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black py-4 rounded-xl transition shadow-lg text-lg">
                Comenzar Rastreo
            </button>
        </div>
    </div>

    <!-- Background Decor -->
    <div class="absolute top-0 left-0 w-full h-96 bg-gray-900 -z-10 shadow-xl"></div>
    <div class="absolute top-0 left-0 w-full h-2 bg-yellow-400 -z-10"></div>

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-gray-100 relative z-10 transition-all duration-500 overflow-hidden" id="card">
        
        <!-- Delivery Hero Header -->
        @if($order->delivery_type === 'delivery')
        <div class="delivery-hero" id="delivery-hero">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-yellow-400 font-black text-xs uppercase tracking-widest mb-1">🛵 Seguimiento de Domicilio</p>
                    <h1 class="text-2xl font-black text-white">Pedido #{{ $order->id }}</h1>
                    <p class="text-gray-400 text-sm font-medium mt-1">Hola, {{ $order->customer_name ?: 'Cliente' }} 👋</p>
                </div>
                <div class="text-right">
                    <p class="text-white font-black text-xl">${{ number_format($order->total, 0) }}</p>
                    <p class="text-gray-400 text-xs">Total</p>
                </div>
            </div>

            @if($order->delivery_address)
            <div class="bg-white/10 rounded-xl p-3 flex items-start gap-3 mt-2">
                <span class="text-2xl">📍</span>
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-widest">Entrega en</p>
                    <p class="text-white font-bold text-sm">{{ $order->delivery_address }}</p>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="p-6 pb-0 text-center">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-1">Tu Pedido #{{ $order->id }}</h1>
            <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">Hola, {{ $order->customer_name ?: 'Cliente' }}</p>
        </div>
        @endif

        <div class="p-6">

        <!-- Steps Progress -->
        <div class="flex justify-between mb-6 mt-2" id="steps-container">
            <div class="step-item">
                <div class="step-circle done" id="circle-pending">✓</div>
                <div class="step-label done" id="label-pending">Recibido</div>
            </div>
            <div class="step-item">
                <div class="step-circle" id="circle-preparing">🍳</div>
                <div class="step-label" id="label-preparing">Cocina</div>
            </div>
            @if($order->delivery_type === 'delivery')
            <div class="step-item">
                <div class="step-circle" id="circle-on_way">🛵</div>
                <div class="step-label" id="label-on_way">En Camino</div>
            </div>
            @endif
            <div class="step-item">
                <div class="step-circle" id="circle-completed">🎉</div>
                <div class="step-label" id="label-completed">Entregado</div>
            </div>
        </div>

        <!-- Status Box -->
        <div class="rounded-2xl p-5 mb-6 relative overflow-hidden flex flex-col justify-center items-center min-h-[140px] shadow-sm transition-all duration-300" id="status-box"
             style="background: linear-gradient(135deg, #fef3c7, #fff7e6); border: 1px solid #fde68a;">
            
            <!-- Road Animation Scene (Hidden by default, shown when on_way) -->
            <div id="moto-container" class="road-scene absolute w-full h-full top-0 left-0">
                <div class="sun">☀️</div>
                <div class="cloud">☁️</div>
                <div class="cloud">☁️</div>
                <div class="building">🏢</div>
                <div class="building">🏪</div>
                
                <div class="moto-rider" id="moto-icon">🛵</div>
                <div class="exhaust">
                    <div class="exhaust-puff"></div>
                    <div class="exhaust-puff"></div>
                    <div class="exhaust-puff"></div>
                </div>
                
                <div class="road-surface">
                    <div class="road-line"></div>
                    <div class="road-line"></div>
                    <div class="road-line"></div>
                    <div class="road-line"></div>
                    <div class="road-line"></div>
                </div>
            </div>

            <p class="text-gray-800 font-black text-lg text-center relative z-10 transition-all duration-300 bg-white/70 backdrop-blur-sm px-4 py-2 rounded-xl mt-2" id="status-text">
                ¡Recibimos tu pedido y lo estamos procesando! 🚀
            </p>
        </div>

        <!-- Detalles de la compra -->
        <div class="border-t border-gray-100 pt-5">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Resumen</p>
            <div class="space-y-2 mb-4 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($order->items as $item)
                <div class="flex justify-between items-start text-sm">
                    <span class="font-bold text-gray-800">{{ $item->quantity }}x {{ $item->product->name }}</span>
                    <span class="font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 0) }}</span>
                </div>
                @endforeach
            </div>
            
            @if($order->delivery_type === 'delivery')
            <div class="flex justify-between items-start text-sm text-gray-500 mb-2">
                <span class="font-bold">🛵 Domicilio</span>
                <span class="font-bold">${{ number_format($order->delivery_fee, 0) }}</span>
            </div>
            @endif

            <div class="flex justify-between text-xl font-black text-gray-900 pt-3 border-t border-gray-100">
                <span>TOTAL</span>
                <span class="text-yellow-500">${{ number_format($order->total, 0) }}</span>
            </div>
        </div>
        
        <!-- Feedback Section (Hidden by default) -->
        <div id="feedback-section" class="hidden mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-center font-black text-gray-800 text-lg mb-3">¿Qué tal estuvo tu pedido?</h3>
            <div class="flex justify-center gap-2 mb-4">
                <button onclick="setRating(1)" class="text-4xl text-gray-300 hover:scale-110 transition" id="star-1">★</button>
                <button onclick="setRating(2)" class="text-4xl text-gray-300 hover:scale-110 transition" id="star-2">★</button>
                <button onclick="setRating(3)" class="text-4xl text-gray-300 hover:scale-110 transition" id="star-3">★</button>
                <button onclick="setRating(4)" class="text-4xl text-gray-300 hover:scale-110 transition" id="star-4">★</button>
                <button onclick="setRating(5)" class="text-4xl text-gray-300 hover:scale-110 transition" id="star-5">★</button>
            </div>
            <textarea id="feedback-comments" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-yellow-400 mb-3 font-bold text-gray-700" placeholder="Déjanos tus comentarios (opcional)..."></textarea>
            <button id="feedback-btn" onclick="submitFeedback()" class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black py-3 rounded-xl transition shadow-md">
                Enviar Calificación
            </button>
        </div>
        
        </div><!-- end .p-6 -->
    </div>

    <!-- Audio -->
    <audio id="readySound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
    <audio id="progressSound" src="https://assets.mixkit.co/active_storage/sfx/2018/2018-preview.mp3" preload="auto"></audio>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const orderId = {{ $order->id }};
        const deliveryType = '{{ $order->delivery_type }}';
        let currentStatus = '{{ $order->customer_confirmed_at && $order->status !== 'completed' ? 'customer_confirmed' : $order->status }}';
        
        // Modal de activación de sonido obligatorio
        document.getElementById('activate-sound-btn').addEventListener('click', function() {
            // 1. Ocultar el modal INMEDIATAMENTE
            document.getElementById('sound-overlay').classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
            setTimeout(() => {
                document.getElementById('sound-overlay').style.display = 'none';
            }, 500);

            // 2. Intentar cargar el audio y notificaciones de forma segura
            try {
                document.getElementById('readySound').load();
                document.getElementById('progressSound').load();
                const playPromise = document.getElementById('progressSound').play();
                
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        document.getElementById('progressSound').pause();
                    }).catch(error => {
                        console.log('Auto-play prevent default:', error);
                    });
                }
                
                if ("Notification" in window) {
                    Notification.requestPermission();
                }
            } catch (e) {
                console.log('Error initializing audio', e);
            }
        });

        // Remove old references that no longer exist in HTML
        // step-on_way is now a circle not a separate hidden div
        // The delivery steps are already shown conditionally in Blade

        let socket;
        if (typeof io !== 'undefined') {
            socket = io(
                (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')
                ? 'http://' + window.location.hostname + ':3000'
                : undefined
            );

            socket.on('connect', () => {
                console.log('Conectado al localizador virtual');
                socket.emit('join-order', orderId);
            });

            socket.on('order-status-changed', (data) => {
                if (String(data.orderId) === String(orderId) && data.status !== currentStatus) {
                    currentStatus = data.status;
                    updateUI(currentStatus, true);
                }
            });
        } else {
            console.warn("Socket.io no está disponible. No habrá actualizaciones en tiempo real.");
        }
        
        // Inicializar UI inmediatamente para no depender del socket
        updateUI(currentStatus, false);

        function setStepState(stepId, state) {
            const circle = document.getElementById('circle-' + stepId);
            const label = document.getElementById('label-' + stepId);
            if (!circle || !label) return;
            circle.classList.remove('active', 'done');
            label.classList.remove('active', 'done');
            if (state === 'active') {
                circle.classList.add('active');
                label.classList.add('active');
            } else if (state === 'done') {
                circle.classList.add('done');
                label.classList.add('done');
            }
        }

        function setStatusBoxStyle(color) {
            const box = document.getElementById('status-box');
            if (!box) return;
            const styles = {
                yellow: 'background: linear-gradient(135deg, #fef3c7, #fff7e6); border: 1px solid #fde68a;',
                orange: 'background: linear-gradient(135deg, #ffedd5, #fff7ed); border: 1px solid #fed7aa;',
                blue: 'background: linear-gradient(135deg, #dbeafe, #eff6ff); border: 1px solid #bfdbfe;',
                green: 'background: linear-gradient(135deg, #dcfce7, #f0fdf4); border: 1px solid #bbf7d0;',
                red: 'background: linear-gradient(135deg, #fee2e2, #fef2f2); border: 1px solid #fca5a5;',
            };
            box.style.cssText = styles[color] || styles.yellow;
        }

        function updateUI(status, playAlert = false) {
            const textEl = document.getElementById('status-text');
            const motoContainer = document.getElementById('moto-container');
            const motoIcon = document.getElementById('moto-icon');
            const sound = document.getElementById('readySound');
            const pSound = document.getElementById('progressSound');
            
            motoContainer.classList.add('hidden', 'opacity-0');
            motoIcon.style.left = '-50px';
            
            // Reset all steps
            ['pending','preparing','on_way','completed'].forEach(s => setStepState(s, ''));
            setStepState('pending', 'done'); // always done
            
            // Sonido corto mágico en avance de estado (excepto cuando está listo)
            if (playAlert && status !== 'ready') {
                pSound.currentTime = 0;
                pSound.play().catch(e => console.log('Bloqueado', e));
            }

            if (status === 'pending') {
                setStatusBoxStyle('yellow');
                textEl.innerText = '¡Recibimos tu pedido y lo estamos procesando! 🚀';
            } else if (status === 'preparing') {
                setStepState('preparing', 'active');
                setStatusBoxStyle('orange');
                textEl.innerText = 'Tu pedido está en preparación en cocina 👨‍🍳';
            } else if (status === 'ready' && deliveryType === 'local') {
                setStepState('preparing', 'done');
                setStepState('completed', 'active');
                setStatusBoxStyle('green');
                textEl.innerText = '¡Tu pedido está listo en la barra! 🍨';
                if (playAlert) {
                    fireNotification('¡Tu pedido ya está listo!', 'Acércate a la barra a recogerla.');
                }
            } else if (status === 'ready' && deliveryType === 'delivery') {
                setStepState('preparing', 'done');
                setStepState('on_way', 'active');
                setStatusBoxStyle('orange');
                textEl.innerText = '¡Tu pedido está listo y pronto saldrá hacia tu dirección! 🛍️';
                if (playAlert) {
                    fireNotification('¡Tu pedido ya está listo!', 'Pronto saldrá hacia tu dirección.');
                }
            } else if (status === 'on_way') {
                setStepState('preparing', 'done');
                setStepState('on_way', 'active');
                setStatusBoxStyle('blue');
                textEl.innerText = '¡El domiciliario va en camino con tu pedido!';
                textEl.classList.remove('bg-white/70');
                textEl.classList.add('bg-white/90', 'shadow-sm');
                
                if (deliveryType === 'delivery') {
                    let confirmBtn = document.getElementById('confirm-delivery-btn');
                    if (!confirmBtn) {
                        confirmBtn = document.createElement('button');
                        confirmBtn.id = 'confirm-delivery-btn';
                        confirmBtn.className = 'confirm-btn w-full bg-green-500 hover:bg-green-600 text-white font-black py-4 rounded-xl transition shadow-xl mt-4 text-lg';
                        confirmBtn.innerText = '✅ ¡Ya recibí mi pedido!';
                        confirmBtn.onclick = function() { confirmDelivery(); };
                        document.getElementById('feedback-section').parentNode.insertBefore(confirmBtn, document.getElementById('feedback-section'));
                    }
                    confirmBtn.style.display = 'block';
                }
                // Show road animation
                motoContainer.classList.add('visible');
            } else if (status === 'customer_confirmed') {
                let confirmBtn = document.getElementById('confirm-delivery-btn');
                if (confirmBtn) confirmBtn.style.display = 'none';
                
                setStepState('preparing', 'done');
                setStepState('on_way', 'active');
                setStatusBoxStyle('blue');
                textEl.innerText = '¡Gracias por confirmar! Esperando validación final de tienda...';
                textEl.classList.remove('bg-white/70');
                textEl.classList.add('bg-white/90', 'shadow-sm');
                motoContainer.classList.add('visible');
            } else if (status === 'completed') {
                let confirmBtn = document.getElementById('confirm-delivery-btn');
                if (confirmBtn) confirmBtn.style.display = 'none';
                
                setStepState('preparing', 'done');
                setStepState('on_way', 'done');
                setStepState('completed', 'done');
                setStatusBoxStyle('green');
                textEl.innerText = '¡Pedido entregado! Gracias por elegir Sunber. 😋';
                sound.pause();
                Swal.close();
            } else if (status === 'cancelled') {
                setStatusBoxStyle('red');
                textEl.innerText = 'Tu pedido ha sido cancelado. Contacta a soporte.';
            }

            if (status === 'ready' || status === 'completed' || status === 'customer_confirmed') {
                document.getElementById('feedback-section').classList.remove('hidden');
            }
        }

        let currentRating = 0;
        function setRating(stars) {
            currentRating = stars;
            for(let i=1; i<=5; i++) {
                const starEl = document.getElementById('star-'+i);
                if(i <= stars) {
                    starEl.classList.remove('text-gray-300');
                    starEl.classList.add('text-yellow-400');
                } else {
                    starEl.classList.remove('text-yellow-400');
                    starEl.classList.add('text-gray-300');
                }
            }
        }

        function submitFeedback() {
            if(currentRating === 0) {
                Swal.fire('Oops', 'Por favor selecciona al menos una estrella.', 'warning');
                return;
            }
            const comments = document.getElementById('feedback-comments').value;
            const btn = document.getElementById('feedback-btn');
            btn.innerText = 'Enviando...';
            btn.disabled = true;

            fetch(`/api/orders/${orderId}/feedback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    rating: currentRating,
                    feedback_comments: comments
                })
            }).then(res => res.json())
              .then(data => {
                  Swal.fire('¡Gracias!', 'Tu calificación nos ayuda a mejorar.', 'success');
                  document.getElementById('feedback-section').innerHTML = '<p class="text-green-600 font-bold text-center">¡Gracias por tu feedback! 💛</p>';
              }).catch(err => {
                  btn.innerText = 'Enviar Calificación';
                  btn.disabled = false;
              });
        }

        function confirmDelivery() {
            const btn = document.getElementById('confirm-delivery-btn');
            btn.innerText = 'Confirmando...';
            btn.disabled = true;

            fetch(`/api/tracking/${orderId}/confirm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(res => res.json())
              .then(data => {
                  btn.style.display = 'none';
                  Swal.fire('¡Excelente!', 'Gracias por confirmar. ¡Disfruta tu pedido!', 'success');
              }).catch(err => {
                  btn.innerText = '¡Ya recibí mi pedido!';
                  btn.disabled = false;
              });
        }

        function fireNotification(title, text) {
            const sound = document.getElementById('readySound');
            sound.currentTime = 0;
            if (navigator.vibrate) navigator.vibrate([500, 200, 500]);
            sound.loop = true;
            sound.play().catch(e => console.log('Bloqueado por autoplay'));

            if ("Notification" in window && Notification.permission === "granted") {
                const notif = new Notification(title, { body: text });
                notif.onclick = function() {
                    window.focus();
                    sound.pause();
                    sound.currentTime = 0;
                    Swal.close();
                    this.close();
                };
            }

            Swal.fire({
                title: title,
                text: text,
                icon: 'success',
                confirmButtonText: '¡Entendido!',
                confirmButtonColor: '#FBBF24',
                backdrop: `rgba(17,24,39,0.8)`,
                allowOutsideClick: false
            }).then(() => {
                sound.pause();
                sound.currentTime = 0;
            });
        }
    </script>
</body>
</html>
