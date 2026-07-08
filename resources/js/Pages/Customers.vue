<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    customers: Array
});

const searchQuery = ref('');
const selectedCustomer = ref(null);
const customerOrders = ref([]);
const isModalOpen = ref(false);

const filteredCustomers = computed(() => {
    return props.customers.filter(c => {
        const term = searchQuery.value.toLowerCase();
        return (c.name && c.name.toLowerCase().includes(term)) || (c.phone && c.phone.includes(term));
    });
});

const openCustomerDetails = async (customer) => {
    selectedCustomer.value = customer;
    customerOrders.value = [];
    isModalOpen.value = true;
    try {
        const res = await axios.get(`/api/customers/${customer.id}/orders`);
        customerOrders.value = res.data;
    } catch (e) {
        console.error('Error fetching orders:', e);
    }
};

const formatPrice = (val) => Number(val).toLocaleString('es-CO');

const totalCustomers = computed(() => props.customers.length);
const totalPoints = computed(() => props.customers.reduce((acc, c) => acc + c.points, 0));
const totalVips = computed(() => props.customers.filter(c => c.orders_count >= 5).length);
</script>

<template>
    <Head title="Fidelización" />
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Fidelización de Clientes</h1>
                <p class="text-gray-500 font-medium mt-1">Monitorea y premia a tus mejores clientes.</p>
            </div>
            <div class="relative w-full md:w-64">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">🔍</span>
                <input v-model="searchQuery" type="text" placeholder="Buscar cliente..." class="w-full bg-white border border-gray-200 rounded-xl py-2 pl-10 pr-4 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition shadow-sm font-medium">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">👥</div>
                <h3 class="text-blue-600 font-bold uppercase tracking-widest text-xs mb-1">Total Clientes</h3>
                <p class="text-4xl font-black text-blue-900">{{ totalCustomers }}</p>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">⭐</div>
                <h3 class="text-yellow-600 font-bold uppercase tracking-widest text-xs mb-1">Puntos Emitidos</h3>
                <p class="text-4xl font-black text-yellow-900">{{ totalPoints }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">💎</div>
                <h3 class="text-purple-600 font-bold uppercase tracking-widest text-xs mb-1">Clientes VIP</h3>
                <p class="text-4xl font-black text-purple-900">{{ totalVips }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto pb-2 -mb-2">
                <table class="w-full text-left border-collapse min-w-[800px] whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-widest font-bold">
                            <th class="p-5 border-b border-gray-100">Cliente</th>
                            <th class="p-5 border-b border-gray-100">Contacto</th>
                            <th class="p-5 border-b border-gray-100 text-center">Entregas</th>
                            <th class="p-5 border-b border-gray-100 text-center">Puntos Sunber</th>
                            <th class="p-5 border-b border-gray-100 text-right">Nivel</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium text-gray-800">
                        <tr v-for="customer in filteredCustomers" :key="customer.id" @click="openCustomerDetails(customer)" class="hover:bg-yellow-50 cursor-pointer transition border-b border-gray-50 last:border-0">
                            <td class="p-5">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-yellow-200 to-yellow-400 flex items-center justify-center text-yellow-900 font-black shadow-sm">
                                        {{ customer.name ? customer.name.charAt(0).toUpperCase() : 'C' }}
                                    </div>
                                    <span class="font-bold text-gray-900 text-base">{{ customer.name || 'Cliente sin nombre' }}</span>
                                </div>
                            </td>
                            <td class="p-5 text-gray-500 font-bold">{{ customer.phone }}</td>
                            <td class="p-5 text-center">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg font-bold border border-gray-200">
                                    🛍️ {{ customer.orders_count }}
                                </span>
                            </td>
                            <td class="p-5 text-center font-black text-yellow-600 text-lg">{{ customer.points }}</td>
                            <td class="p-5 text-right">
                                <span v-if="customer.orders_count >= 5" class="bg-purple-100 text-purple-700 text-xs font-black px-3 py-1.5 rounded-lg uppercase tracking-wider border border-purple-200 shadow-sm">💎 VIP</span>
                                <span v-else class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1.5 rounded-lg uppercase tracking-wider border border-blue-100">Regular</span>
                            </td>
                        </tr>
                        <tr v-if="filteredCustomers.length === 0">
                            <td colspan="5" class="p-12 text-center">
                                <div class="text-6xl mb-4">🔍</div>
                                <p class="text-gray-400 font-bold text-lg">No se encontraron clientes.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CUSTOMER DETAILS MODAL -->
        <Teleport to="body">
        <div v-if="isModalOpen" @click.self="isModalOpen = false" @keydown.esc.window="isModalOpen = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center lg:justify-end">
            <div class="bg-white h-auto lg:h-full w-full max-w-md shadow-2xl flex flex-col max-h-[85vh] lg:max-h-none rounded-t-3xl lg:rounded-none animate-slide-up lg:animate-slide-in-right">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-xl bg-yellow-400 flex items-center justify-center text-yellow-900 font-black text-xl">
                            {{ selectedCustomer.name ? selectedCustomer.name.charAt(0).toUpperCase() : 'C' }}
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800">{{ selectedCustomer.name || 'Cliente' }}</h2>
                            <p class="text-sm text-gray-500 font-bold">{{ selectedCustomer.phone }}</p>
                        </div>
                    </div>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-800">✕</button>
                </div>
                
                <div class="p-6 bg-gradient-to-br from-yellow-50 to-white flex gap-4 border-b border-yellow-100">
                    <div class="flex-1 bg-white p-4 rounded-xl shadow-sm border border-yellow-100 text-center">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pedidos</p>
                        <p class="text-2xl font-black text-gray-800">{{ selectedCustomer.orders_count }}</p>
                    </div>
                    <div class="flex-1 bg-white p-4 rounded-xl shadow-sm border border-yellow-100 text-center">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Puntos</p>
                        <p class="text-2xl font-black text-yellow-600">{{ selectedCustomer.points }}</p>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-gray-50 custom-scrollbar">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4">Historial de Compras</h3>
                    
                    <div v-if="customerOrders.length === 0" class="text-center py-8 text-gray-400">
                        <div class="text-4xl mb-2 opacity-50">🛍️</div>
                        <p class="font-bold text-sm">Cargando o sin historial...</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="order in customerOrders" :key="order.id" class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-bold text-gray-500">{{ new Date(order.created_at).toLocaleDateString('es-CO') }}</span>
                                <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded">{{ order.status }}</span>
                            </div>
                            <div class="space-y-1 mb-3">
                                <div v-for="item in order.items" :key="item.id" class="text-sm text-gray-700">
                                    <span class="font-bold">{{ item.quantity }}x</span> {{ item.product.name }}
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                <span class="text-xs font-bold text-gray-500">{{ order.payment_method }}</span>
                                <span class="font-black text-gray-900">${{ formatPrice(order.total) }}</span>
                            </div>
                            <div v-if="order.promotion_id" class="mt-2 text-xs font-bold text-green-600 bg-green-50 p-2 rounded-lg">
                                ⭐ Promoción Aplicada
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
.animate-slide-in-right {
    animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
@keyframes slide-up {
    0% { transform: translateY(100%); }
    100% { transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #D1D5DB; border-radius: 20px; }
</style>
