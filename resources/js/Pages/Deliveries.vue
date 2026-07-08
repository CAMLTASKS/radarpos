<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    deliveries: Array
});

// Kanban Columns
const pendingOrders = computed(() => props.deliveries.filter(o => o.status === 'pending'));
const preparingOrders = computed(() => props.deliveries.filter(o => o.status === 'preparing'));
const readyOrders = computed(() => props.deliveries.filter(o => o.status === 'ready'));
const onWayOrders = computed(() => props.deliveries.filter(o => o.status === 'on_way'));

const now = ref(new Date());
let timer;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 60000);
});
onUnmounted(() => clearInterval(timer));

const timeSince = (dateString) => {
    const start = new Date(dateString);
    const diffMs = now.value - start;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 60) return `Hace ${diffMins} min`;
    const diffHrs = Math.floor(diffMins / 60);
    return `Hace ${diffHrs} h ${diffMins % 60} m`;
};

const updateStatus = async (order, newStatus, deliveryTime = null) => {
    try {
        const payload = { status: newStatus };
        if (deliveryTime) payload.delivery_time = deliveryTime;

        await axios.patch(`/api/orders/${order.id}/status`, payload);
        router.reload();
    } catch (e) {
        alert('Error al actualizar estado');
    }
};

const cancelOrder = async (order) => {
    const reason = prompt('Por favor, ingresa el motivo de la cancelación:');
    if (reason === null) return;
    if (reason.trim() === '') {
        alert('El motivo es obligatorio para cancelar.');
        return;
    }
    try {
        await axios.patch(`/api/orders/${order.id}/status`, { status: 'cancelled', cancellation_reason: reason });
        router.reload();
    } catch (e) {
        alert('Error al cancelar');
    }
};
</script>

<template>
    <Head title="Kanban de Órdenes" />
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 bg-white p-5 rounded-2xl shadow-sm border border-gray-200 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900">Cocina & Pedidos</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Kanban de preparación para ventas y domicilios.</p>
            </div>
            <div class="flex space-x-2 w-full md:w-auto">
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full font-bold text-sm shadow-sm w-full md:w-auto text-center">Activas: {{ pendingOrders.length + preparingOrders.length + readyOrders.length + onWayOrders.length }}</span>
            </div>
        </div>

        <div class="flex flex-1 gap-4 lg:gap-6 h-[75vh] lg:h-[calc(100vh-220px)] overflow-x-auto pb-4 custom-scrollbar snap-x snap-mandatory">
            
            <!-- Columna 1: Recepción -->
            <div class="w-[85vw] sm:w-[350px] shrink-0 bg-gray-100 rounded-3xl p-4 flex flex-col border border-gray-200 shadow-inner snap-center">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h2 class="font-black text-gray-700 uppercase tracking-widest text-sm flex items-center">
                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Recepción (Nuevos)
                    </h2>
                    <span class="bg-gray-200 text-gray-600 font-bold text-xs px-2 py-1 rounded-lg">{{ pendingOrders.length }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                    <div v-for="order in pendingOrders" :key="order.id" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 hover:shadow-md transition relative">
                        <div class="absolute top-2 right-2 text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                            ⏱️ {{ timeSince(order.created_at) }}
                        </div>
                        <div class="flex justify-between items-start mb-2 border-b border-gray-100 pb-2 mt-4">
                            <div>
                                <p class="font-black text-gray-900">#{{ order.id }} - {{ order.customer_name || 'Desconocido' }}</p>
                                <div class="text-xs font-semibold mt-1 flex gap-2 items-center">
                                    <span v-if="order.delivery_type === 'delivery'" class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-md">🚚 Domicilio</span>
                                    <span v-else class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md">🍽️ Mesa/Local</span>
                                    <span class="font-bold text-gray-500">Dir: {{ order.delivery_address || 'N/A' }}</span>
                                </div>
                            </div>
                            <span class="font-black text-yellow-600">${{ parseFloat(order.total).toFixed(2) }}</span>
                        </div>
                        <ul class="text-xs font-bold text-gray-600 space-y-1 mb-4">
                            <li v-for="item in order.items" :key="item.id">👉 {{ item.quantity }}x {{ item.product.name }}</li>
                        </ul>
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Aceptar y notificar cocina:</p>
                            <button @click="updateStatus(order, 'preparing')" class="w-full py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold text-xs rounded-xl shadow-sm transition">
                                👨‍🍳 Mandar a Preparación
                            </button>
                            <button @click="cancelOrder(order)" class="w-full py-2 bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs rounded-xl transition">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 2: En Preparación -->
            <div class="w-[85vw] sm:w-[350px] shrink-0 bg-gray-100 rounded-3xl p-4 flex flex-col border border-gray-200 shadow-inner snap-center">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h2 class="font-black text-gray-700 uppercase tracking-widest text-sm flex items-center">
                        <span class="w-2 h-2 rounded-full bg-orange-400 mr-2 animate-pulse"></span> En Preparación
                    </h2>
                    <span class="bg-gray-200 text-gray-600 font-bold text-xs px-2 py-1 rounded-lg">{{ preparingOrders.length }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                    <div v-for="order in preparingOrders" :key="order.id" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 border-l-4 border-l-orange-400 hover:shadow-md transition relative">
                        <div class="absolute top-2 right-2 text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                            ⏱️ {{ timeSince(order.created_at) }}
                        </div>
                        <div class="mb-3 mt-4">
                            <p class="font-black text-gray-900">#{{ order.id }} - {{ order.customer_name || 'Desconocido' }}</p>
                            <div class="text-xs font-semibold mt-1 flex gap-2 items-center">
                                <span v-if="order.delivery_type === 'delivery'" class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-md">🚚 Domicilio</span>
                                <span v-else class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md">🍽️ Mesa/Local</span>
                            </div>
                        </div>
                        
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Acción de Cocina:</p>
                        <div class="flex gap-2">
                            <button @click="updateStatus(order, 'ready')" class="flex-1 py-2 bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 font-bold text-xs rounded-xl transition shadow-sm">
                                ✅ Listo
                            </button>
                            <a :href="'/orders/' + order.id + '/receipt'" target="_blank" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 rounded-lg font-bold transition shadow-sm text-sm flex items-center justify-center" title="Imprimir Comanda">
                                🖨️
                            </a>
                            <button @click="cancelOrder(order)" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 rounded-lg font-bold transition shadow-sm text-sm flex items-center justify-center" title="Cancelar Orden">
                                ❌
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 3: Listos para Despachar -->
            <div class="w-[85vw] sm:w-[350px] shrink-0 bg-green-50 rounded-3xl p-4 flex flex-col border border-green-200 shadow-inner snap-center">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h2 class="font-black text-green-700 uppercase tracking-widest text-sm flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Listos para Despachar
                    </h2>
                    <span class="bg-green-200 text-green-700 font-bold text-xs px-2 py-1 rounded-lg">{{ readyOrders.length }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                    <div v-for="order in readyOrders" :key="order.id" class="bg-white rounded-2xl p-4 shadow-sm border border-green-200 border-l-4 border-l-green-500 hover:shadow-md transition relative">
                        <div class="absolute top-2 right-2 text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                            ⏱️ {{ timeSince(order.created_at) }}
                        </div>
                        <div class="mb-3 mt-4">
                            <p class="font-black text-gray-900">#{{ order.id }} - {{ order.customer_name || 'Desconocido' }}</p>
                            <div class="text-xs font-semibold mt-1 flex gap-2 items-center flex-wrap">
                                <span v-if="order.delivery_type === 'delivery'" class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-md">🚚 Domicilio</span>
                                <span v-else class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md">🍽️ Mesa/Local</span>
                                <span v-if="order.delivery_address" class="text-gray-500 truncate max-w-[120px]">📍 {{ order.delivery_address }}</span>
                            </div>
                        </div>
                        
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Despachar:</p>
                        <div class="flex gap-2">
                            <button @click="updateStatus(order, 'on_way')" class="flex-1 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md transition">
                                🛵 Mandar en Camino
                            </button>
                            <button v-if="order.delivery_type === 'local'" @click="updateStatus(order, 'completed')" class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                ✅ Entregar
                            </button>
                            <a :href="'/orders/' + order.id + '/receipt'" target="_blank" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 rounded-xl font-bold transition shadow-sm text-sm flex items-center justify-center" title="Imprimir">
                                🖨️
                            </a>
                            <button @click="cancelOrder(order)" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 rounded-xl font-bold transition shadow-sm text-sm flex items-center justify-center" title="Cancelar">
                                ❌
                            </button>
                        </div>
                    </div>
                    <p v-if="!readyOrders.length" class="text-center text-gray-400 text-sm">Sin pedidos listos.</p>
                </div>
            </div>

            <!-- Columna 4: En Camino -->
            <div class="w-[85vw] sm:w-[350px] shrink-0 bg-blue-50 rounded-3xl p-4 flex flex-col border border-blue-200 shadow-inner snap-center">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h2 class="font-black text-blue-700 uppercase tracking-widest text-sm flex items-center">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span> En Camino
                    </h2>
                    <span class="bg-blue-200 text-blue-700 font-bold text-xs px-2 py-1 rounded-lg">{{ onWayOrders.length }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                    <div v-for="order in onWayOrders" :key="order.id" class="bg-white rounded-2xl p-4 shadow-sm border border-blue-200 border-l-4 border-l-blue-500 hover:shadow-md transition relative">
                        <div class="absolute top-2 right-2 text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                            ⏱️ {{ timeSince(order.created_at) }}
                        </div>
                        <div class="mb-3 mt-4">
                            <p class="font-black text-gray-900">#{{ order.id }} - {{ order.customer_name || 'Desconocido' }}</p>
                            <div v-if="order.customer_confirmed_at" class="mt-1 bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-md border border-green-300 w-fit animate-pulse">
                                ✅ Cliente confirmó entrega
                            </div>
                            <div class="text-xs font-semibold mt-1 flex gap-2 items-center flex-wrap">
                                <span v-if="order.delivery_type === 'delivery'" class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-md">🛵 Domicilio</span>
                                <span v-else class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md">🍽️ Local</span>
                                <span v-if="order.delivery_driver" class="text-gray-500">Rep: {{ order.delivery_driver }}</span>
                            </div>
                            <p v-if="order.delivery_address" class="text-xs text-gray-400 mt-1 truncate">📍 {{ order.delivery_address }}</p>
                        </div>
                        
                        <div class="flex gap-2 mt-2">
                            <button @click="updateStatus(order, 'completed')" class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white font-black text-sm rounded-xl shadow-md transition flex items-center justify-center">
                                ✅ Entregado
                            </button>
                            <a :href="'/orders/' + order.id + '/receipt'" target="_blank" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 rounded-xl font-bold transition shadow-sm text-sm flex items-center justify-center" title="Imprimir Comanda">
                                🖨️
                            </a>
                            <button @click="cancelOrder(order)" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 rounded-xl font-bold transition shadow-sm text-sm flex items-center justify-center" title="Cancelar Orden">
                                ❌
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold text-center mt-2">Sumará a métricas y puntos</p>
                    </div>
                    <p v-if="!onWayOrders.length" class="text-center text-gray-400 text-sm">Nadie en camino aún.</p>
                </div>
            </div>

        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #D1D5DB; 
  border-radius: 20px;
}
</style>
