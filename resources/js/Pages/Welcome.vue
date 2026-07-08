<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Bienvenido a Sunber ERP" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center relative overflow-hidden font-sans">
        
        <!-- Background Decorations -->
        <div class="absolute top-0 left-0 w-full h-96 bg-yellow-400 transform -skew-y-6 origin-top-left -z-10 shadow-xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500 rounded-full blur-3xl opacity-20 -z-10"></div>
        
        <div class="bg-white/80 backdrop-blur-xl p-10 rounded-3xl shadow-2xl border border-white/50 w-full max-w-lg text-center transform transition duration-500 hover:scale-105">
            
            <div class="w-40 h-40 mx-auto bg-white rounded-full flex items-center justify-center shadow-lg border-4 border-yellow-400 mb-8 -mt-24">
                <img src="/images/logo.png" alt="Sunber Logo" class="w-32 object-contain" />
            </div>

            <h1 class="text-4xl font-black text-gray-900 mb-1 tracking-tight">RADAR POS</h1>
            <p class="text-gray-500 font-medium mb-8 mt-2">Gestión Inteligente de Puntos de Venta</p>

            <div class="space-y-4">
                <Link v-if="$page.props.auth.user" :href="route('pos')" class="block w-full py-4 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold rounded-2xl shadow-lg shadow-yellow-400/30 transition transform hover:-translate-y-1">
                    Entrar al Sistema (POS)
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="block w-full py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-2xl shadow-lg transition transform hover:-translate-y-1">
                        Iniciar Sesión
                    </Link>
                </template>
            </div>
            
            <div class="mt-10 border-t border-gray-100 pt-6">
                <p class="text-xs text-gray-400 font-bold tracking-widest uppercase">Desarrollado por INGEDEV SOLUTIONS</p>
            </div>
        </div>

    </div>
</template>
