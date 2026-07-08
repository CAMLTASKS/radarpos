<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const isActive = (path) => {
    const url = usePage().url;
    const cleanUrl = url.startsWith('/heladeria/public') ? url.substring('/heladeria/public'.length) : url;
    return cleanUrl.startsWith(path);
};
const isMobileMenuOpen = ref(false);

const notifications = ref({ pending: 0, delayed: 0, comments: 0 });
const showNotifications = ref(false);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data;
    } catch (e) {
        console.error("Error fetching notifications", e);
    }
};

const totalNotifications = computed(() => {
    return notifications.value.pending + notifications.value.delayed + notifications.value.comments;
});

onMounted(() => {
    fetchNotifications();
    setInterval(fetchNotifications, 60000); // Check every minute
});
</script>
<template>
    <div class="h-screen flex bg-gray-50 font-sans text-gray-900 overflow-hidden">
        
        <!-- SIDEBAR ULTRA-MODERNA (Dark Theme) -->
        <aside class="w-72 bg-gray-900 text-gray-300 flex flex-col transition-all duration-300 shrink-0 hidden lg:flex shadow-2xl relative z-20">
            <!-- Logo Section -->
            <div class="h-24 flex items-center justify-center border-b border-gray-800 px-6">
                <div class="bg-white rounded-xl p-2 w-full flex flex-col items-center justify-center shadow-sm">
                    <div class="flex items-center space-x-2">
                        <img src="/images/logo.png" alt="Sunber" class="h-8 object-contain" />
                        <span class="text-gray-900 font-black tracking-widest text-lg">RADAR POS</span>
                    </div>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 custom-scrollbar">
                
                <!-- Dashboard Link -->
                <Link :href="route('dashboard')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group mt-4 mb-2" :class="isActive('/dashboard') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Inicio / Dashboard
                </Link>

                <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 mt-4">Ventas & Logística</p>

                <Link :href="route('pos')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/pos') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Punto de Venta
                </Link>

                <Link :href="route('deliveries')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/deliveries') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    Órdenes (KDS)
                </Link>

                <Link :href="route('cash-register')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/cash-register') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Control de Caja
                </Link>

                <div v-if="$page.props.auth.user.role === 'admin'">
                    <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 mt-8">Administración</p>

                    <Link :href="route('products')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/products') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Menú & Productos
                    </Link>

                    <Link :href="route('inventory')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/inventory') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Insumos (Bodega)
                    </Link>

                    <Link :href="route('customers')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/customers') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Fidelización
                    </Link>

                    <Link :href="route('sales')" class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group" :class="isActive('/sales') ? 'bg-yellow-400 text-gray-900 font-bold shadow-lg shadow-yellow-400/20' : 'hover:bg-gray-800 hover:text-white font-medium'">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Reportes Financieros
                    </Link>

                    <Link :href="route('users.index')" class="flex items-center space-x-3 px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-xl transition" :class="{ 'bg-yellow-400/10 text-yellow-400': $page.url.startsWith('/users') }">
                        <span class="text-xl">👥</span>
                        <span class="font-medium">Usuarios</span>
                    </Link>
                    
                    <Link :href="route('settings.index')" class="flex items-center space-x-3 px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white rounded-xl transition" :class="{ 'bg-yellow-400/10 text-yellow-400': $page.url.startsWith('/settings') }">
                        <span class="text-xl">⚙️</span>
                        <span class="font-medium">Configuración</span>
                    </Link>
                </div>
            </nav>

            <!-- User Profile Section -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center space-x-3 p-3 bg-gray-800 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-lg shadow-inner uppercase">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-yellow-400 uppercase font-black tracking-widest truncate">{{ $page.props.auth.user.role }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- CONTENEDOR PRINCIPAL -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            
            <!-- HEADER SUPERIOR -->
            <header class="h-16 lg:h-20 bg-white/95 backdrop-blur-xl border-b border-gray-100 flex items-center justify-between px-4 lg:px-8 shrink-0 z-30 shadow-sm sticky top-0">
                
                <!-- Logo para Mobile -->
                <div class="lg:hidden flex items-center space-x-2">
                    <img src="/images/logo.png" alt="Sunber" class="h-8 object-contain" />
                    <span class="text-gray-900 font-black tracking-widest text-sm leading-none">RADAR POS</span>
                </div>

                <!-- Buscador Universal -->
                <div class="hidden md:flex items-center bg-gray-100/80 rounded-full px-4 py-2 w-64 lg:w-96 border border-gray-200 focus-within:border-yellow-400 focus-within:bg-white focus-within:shadow-sm transition-all ml-auto lg:ml-0 mr-4 lg:mr-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Buscar pedidos, productos..." class="bg-transparent border-0 focus:ring-0 text-sm ml-2 w-full text-gray-700 placeholder-gray-400">
                </div>

                <!-- Notificaciones & Hora -->
                <div class="flex items-center space-x-3 lg:space-x-6 relative">
                    <button class="md:hidden p-2 text-gray-500 hover:text-gray-700 transition bg-gray-100/80 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                    
                    <div class="text-right hidden lg:block">
                        <p class="text-sm font-bold text-gray-800">Caja Abierta</p>
                        <p class="text-xs text-green-500 font-bold">Turno: Mañana</p>
                    </div>
                    <button @click="showNotifications = !showNotifications" class="relative p-2 text-gray-500 hover:text-gray-800 transition bg-gray-100/80 lg:bg-transparent rounded-full lg:rounded-none">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span v-if="totalNotifications > 0" class="absolute top-0 right-0 lg:top-1 lg:right-1 w-2.5 h-2.5 lg:w-3 lg:h-3 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Panel de Notificaciones -->
                    <div v-if="showNotifications" class="absolute top-full right-0 mt-2 lg:mt-4 w-72 lg:w-80 bg-white rounded-2xl lg:rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-50 origin-top-right">
                        <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <h3 class="font-black text-gray-900">Notificaciones</h3>
                            <span class="text-xs font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">{{ totalNotifications }} nuevas</span>
                        </div>
                        <div class="p-4 space-y-3">
                            <Link :href="route('deliveries')" class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100" v-if="notifications.delayed > 0">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-500 shrink-0">⚠️</div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Órdenes Demoradas</p>
                                    <p class="text-xs text-gray-500">Hay {{ notifications.delayed }} orden(es) con más de 30 min de espera.</p>
                                </div>
                            </Link>

                            <Link :href="route('deliveries')" class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100" v-if="notifications.pending > 0">
                                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 shrink-0">⏳</div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Nuevas Órdenes</p>
                                    <p class="text-xs text-gray-500">Hay {{ notifications.pending }} orden(es) pendientes por aceptar.</p>
                                </div>
                            </Link>

                            <Link :href="route('comments')" class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100" v-if="notifications.comments > 0">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 shrink-0">💬</div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Nuevos Comentarios</p>
                                    <p class="text-xs text-gray-500">Recibiste {{ notifications.comments }} comentario(s) hoy.</p>
                                </div>
                            </Link>

                            <div v-if="totalNotifications === 0" class="text-center py-6 text-gray-400">
                                <span class="text-4xl block mb-2">🎉</span>
                                <p class="text-sm font-bold">¡Todo al día!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 relative scroll-smooth">
                <!-- Decorative subtle background blob -->
                <div class="absolute z-[-1] top-0 right-0 -mr-40 -mt-40 w-96 h-96 rounded-full bg-yellow-100/50 blur-3xl pointer-events-none"></div>
                <div class="absolute z-[-1] bottom-0 left-0 -ml-40 -mb-40 w-96 h-96 rounded-full bg-blue-50/50 blur-3xl pointer-events-none"></div>
                
                <div class="relative max-w-7xl mx-auto min-h-full flex flex-col pb-24 lg:pb-8">
                    <slot />
                </div>
            </main>
        </div>

        <nav class="fixed bottom-0 w-full bg-white/90 backdrop-blur-xl border-t border-gray-200 flex lg:hidden justify-around items-center h-16 sm:h-20 px-2 z-40 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] pb-safe">
            <Link :href="route('dashboard')" class="flex flex-col items-center justify-center w-full h-full space-y-1" :class="isActive('/dashboard') ? 'text-yellow-500' : 'text-gray-400 hover:text-gray-800'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold">Inicio</span>
            </Link>
            <Link :href="route('pos')" class="flex flex-col items-center justify-center w-full h-full space-y-1" :class="isActive('/pos') ? 'text-yellow-500' : 'text-gray-400 hover:text-gray-800'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="text-[10px] font-bold">POS</span>
            </Link>
            <!-- Flotante o Destacado en el centro -->
            <div class="relative -top-4 w-full flex justify-center">
                <Link :href="route('deliveries')" class="bg-gray-900 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg shadow-gray-900/30 border-4 border-gray-50 transform transition active:scale-95" :class="isActive('/deliveries') ? 'bg-yellow-400 text-gray-900 border-white shadow-yellow-400/40' : ''">
                    <div class="relative">
                       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                       <span v-if="notifications.delayed > 0" class="absolute -top-2 -right-2 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                    </div>
                </Link>
            </div>
            <Link :href="route('cash-register')" class="flex flex-col items-center justify-center w-full h-full space-y-1" :class="isActive('/cash-register') ? 'text-yellow-500' : 'text-gray-400 hover:text-gray-800'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[10px] font-bold">Caja</span>
            </Link>
            <button @click="isMobileMenuOpen = true" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-400 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <span class="text-[10px] font-bold">Menú</span>
            </button>
        </nav>

        <!-- MOBILE FULL MENU OVERLAY -->
        <transition name="fade">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 lg:hidden" @click="isMobileMenuOpen = false"></div>
        </transition>

        <transition name="slide-up">
            <div v-if="isMobileMenuOpen" class="fixed inset-x-0 bottom-0 bg-white rounded-t-3xl z-50 lg:hidden max-h-[85vh] flex flex-col shadow-[0_-20px_60px_rgba(0,0,0,0.15)]">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white rounded-t-3xl sticky top-0 z-10">
                    <h3 class="font-black text-gray-900 text-lg">Menú Principal</h3>
                    <button @click="isMobileMenuOpen = false" class="p-2 bg-gray-100 rounded-full text-gray-500 hover:text-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-6 pb-8 custom-scrollbar bg-gray-50/30">
                    <!-- Section: Logística -->
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-2">Accesos Rápidos</p>
                        <div class="grid grid-cols-2 gap-3">
                            <Link :href="route('dashboard')" class="bg-white p-4 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/dashboard') ? 'border-yellow-400 bg-yellow-50 ring-2 ring-yellow-400/20' : ''">
                                <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span class="text-xs font-bold text-gray-800">Dashboard</span>
                            </Link>
                            <Link :href="route('deliveries')" class="bg-white p-4 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/deliveries') ? 'border-yellow-400 bg-yellow-50 ring-2 ring-yellow-400/20' : ''">
                                <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                <span class="text-xs font-bold text-gray-800">KDS Órdenes</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Section: Admin -->
                    <div v-if="$page.props.auth.user.role === 'admin'">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 px-2">Administración</p>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            <Link :href="route('products')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/products') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Menú & Prod.</span>
                            </Link>
                            <Link :href="route('inventory')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/inventory') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Insumos</span>
                            </Link>
                            <Link :href="route('customers')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/customers') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Fidelización</span>
                            </Link>
                            <Link :href="route('sales')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/sales') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Reportes</span>
                            </Link>
                            <Link :href="route('users.index')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/users') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <span class="text-xl">👥</span>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Usuarios</span>
                            </Link>
                            <Link :href="route('settings.index')" class="bg-white p-3 rounded-2xl flex flex-col items-center justify-center gap-2 border border-gray-100 shadow-sm active:bg-yellow-50" :class="isActive('/settings') ? 'border-yellow-400 bg-yellow-50' : ''">
                                <span class="text-xl">⚙️</span>
                                <span class="text-[10px] font-bold text-gray-800 text-center leading-tight">Ajustes</span>
                            </Link>
                        </div>
                    </div>

                    <!-- User Profile & Logout -->
                    <div class="mt-2 pt-2">
                        <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-lg uppercase shadow-sm">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 truncate max-w-[150px]">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest">{{ $page.props.auth.user.role }}</p>
                                </div>
                            </div>
                            <Link href="/logout" method="post" as="button" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-red-500 shadow-sm border border-gray-100 hover:bg-red-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Scrollbar super fina y moderna */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #374151; /* gray-700 */
  border-radius: 20px;
}

/* Transiciones para el menú móvil */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}

.pb-safe {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
