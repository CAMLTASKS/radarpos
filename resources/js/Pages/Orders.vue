<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

defineProps({
    orders: Array
});

const updateStatus = async (orderId, newStatus) => {
    try {
        await axios.patch(`/api/orders/${orderId}/status`, { status: newStatus });
        // Recargar los datos de la página para ver el cambio
        router.reload({ only: ['orders'] });
    } catch (error) {
        console.error(error);
        alert('Error al actualizar el estado.');
    }
};

const formatPrice = (value) => {
    return Number(value).toLocaleString('es-CO');
};
</script>

<template>
    <Head title="Órdenes" />
    <AdminLayout>
        <div class="p-8">
            <h1 class="text-4xl font-extrabold text-pink-400 mb-8 tracking-tight">📋 Gestión de Órdenes</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Pendientes -->
                <div class="bg-white p-6 rounded-3xl shadow-lg border-4 border-yellow-100">
                    <h2 class="text-xl font-bold text-yellow-600 mb-4 border-b-2 border-yellow-50 pb-2">⏳ Pendientes</h2>
                    <div class="space-y-4">
                        <div v-for="order in orders.filter(o => o.status === 'pending')" :key="order.id" class="bg-yellow-50 p-4 rounded-xl border border-yellow-200 relative overflow-hidden">
                            <div class="absolute top-0 right-0 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white rounded-bl-lg shadow-sm" :class="order.delivery_type === 'delivery' ? 'bg-purple-500' : 'bg-blue-500'">
                                {{ order.delivery_type === 'delivery' ? '🛵 Domicilio' : '🍽️ Local' }}
                            </div>
                            <div class="flex justify-between items-start mb-2 mt-2">
                                <span class="font-black text-gray-800 text-lg">#{{ order.id }}</span>
                                <span class="text-xs font-bold text-gray-500">{{ new Date(order.created_at).toLocaleTimeString() }}</span>
                            </div>
                            <p class="text-sm font-bold text-gray-700">{{ order.customer_name || 'Cliente Mostrador' }}</p>
                            <p class="text-xs text-gray-500 mb-3">{{ order.customer_phone || 'Sin número' }}</p>
                            
                            <ul class="text-sm text-gray-600 mb-4 list-disc pl-4">
                                <li v-for="item in order.items" :key="item.id">
                                    {{ item.quantity }}x {{ item.product.name }}
                                </li>
                            </ul>
                            
                            <div class="flex space-x-2 mt-4">
                                <button @click="updateStatus(order.id, 'ready')" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-2 rounded-lg transition shadow text-sm">
                                    Listo ✅
                                </button>
                                <a :href="'/orders/' + order.id + '/receipt'" target="_blank" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 rounded-lg font-bold transition shadow-sm text-lg flex items-center justify-center" title="Imprimir Comanda">
                                    🖨️
                                </a>
                                <button @click="updateStatus(order.id, 'cancelled')" class="bg-red-100 text-red-600 hover:bg-red-200 px-3 rounded-lg font-bold transition shadow-sm text-lg flex items-center justify-center" title="Cancelar Orden">
                                    ❌
                                </button>
                            </div>
                        </div>
                        <p v-if="!orders.filter(o => o.status === 'pending').length" class="text-center text-gray-400 text-sm">No hay órdenes pendientes.</p>
                    </div>
                </div>

                <!-- Listas -->
                <div class="bg-white p-6 rounded-3xl shadow-lg border-4 border-green-100">
                    <h2 class="text-xl font-bold text-green-600 mb-4 border-b-2 border-green-50 pb-2">🎉 Listas para Recoger</h2>
                    <div class="space-y-4">
                        <div v-for="order in orders.filter(o => o.status === 'ready')" :key="order.id" class="bg-green-50 p-4 rounded-xl border border-green-200 relative overflow-hidden">
                            <div class="absolute top-0 right-0 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white rounded-bl-lg shadow-sm" :class="order.delivery_type === 'delivery' ? 'bg-purple-500' : 'bg-blue-500'">
                                {{ order.delivery_type === 'delivery' ? '🛵 Domicilio' : '🍽️ Local' }}
                            </div>
                            <div class="flex justify-between items-start mb-2 mt-2">
                                <span class="font-black text-gray-800 text-lg">#{{ order.id }}</span>
                                <span class="text-xs font-bold text-gray-500">{{ new Date(order.updated_at).toLocaleTimeString() }}</span>
                            </div>
                            <p class="text-sm font-bold text-gray-700">{{ order.customer_name || 'Cliente Mostrador' }}</p>
                            
                            <div class="flex space-x-2 mt-4">
                                <button @click="updateStatus(order.id, 'completed')" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-lg transition shadow text-sm">
                                    Entregar 🍦
                                </button>
                                <a :href="'/tracking/' + order.id" target="_blank" class="bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center px-3 rounded-lg font-bold transition shadow-sm text-lg" title="Ver QR">
                                    📱
                                </a>
                                <a :href="'/orders/' + order.id + '/receipt'" target="_blank" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 rounded-lg font-bold transition shadow-sm text-lg flex items-center justify-center" title="Imprimir Comanda">
                                    🖨️
                                </a>
                                <button @click="updateStatus(order.id, 'cancelled')" class="bg-red-100 text-red-600 hover:bg-red-200 px-3 rounded-lg font-bold transition shadow-sm text-lg flex items-center justify-center" title="Cancelar Orden">
                                    ❌
                                </button>
                            </div>
                        </div>
                        <p v-if="!orders.filter(o => o.status === 'ready').length" class="text-center text-gray-400 text-sm">No hay órdenes listas.</p>
                    </div>
                </div>

                <!-- Completadas -->
                <div class="bg-white p-6 rounded-3xl shadow-lg border-4 border-gray-100 opacity-75">
                    <h2 class="text-xl font-bold text-gray-600 mb-4 border-b-2 border-gray-50 pb-2">✅ Entregadas (Hoy)</h2>
                    <div class="space-y-4">
                        <div v-for="order in orders.filter(o => o.status === 'completed')" :key="order.id" class="bg-gray-50 p-4 rounded-xl border border-gray-200 relative overflow-hidden">
                            <div class="absolute top-0 right-0 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-white rounded-bl-lg" :class="order.delivery_type === 'delivery' ? 'bg-purple-400' : 'bg-blue-400'">
                                {{ order.delivery_type === 'delivery' ? '🛵 Dom' : '🍽️ Loc' }}
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-black text-gray-700">#{{ order.id }}</span>
                                <span class="font-bold text-gray-500">${{ formatPrice(order.total) }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Entregado a las {{ new Date(order.updated_at).toLocaleTimeString() }}</p>
                        </div>
                        <p v-if="!orders.filter(o => o.status === 'completed').length" class="text-center text-gray-400 text-sm">Aún no hay entregas.</p>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
