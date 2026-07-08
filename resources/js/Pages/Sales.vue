<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    todaySales: Number,
    totalOrders: Number,
    recentOrders: Array
});
</script>

<template>
    <Head title="Reportes" />
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-2 md:gap-0">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Dashboard Financiero</h1>
            <div class="text-sm font-bold text-gray-500">Actualizado hace unos segundos</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-3xl p-6 shadow-lg shadow-yellow-400/30 text-gray-900 relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-10 text-9xl transform translate-x-4 translate-y-4">💰</div>
                <h3 class="text-sm font-black uppercase tracking-widest mb-1 opacity-80">Ventas Hoy</h3>
                <p class="text-4xl font-black">${{ parseFloat(todaySales).toFixed(2) }}</p>
                <p class="text-xs font-bold mt-4">+15% respecto a ayer</p>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-1">Órdenes Atendidas</h3>
                <p class="text-4xl font-black text-gray-900">{{ totalOrders }}</p>
                <p class="text-xs font-bold text-green-500 mt-4">Excelente rendimiento hoy</p>
            </div>

            <div class="bg-gray-900 text-white rounded-3xl p-6 shadow-lg relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-10 text-9xl transform translate-x-4 translate-y-4">📈</div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-1">Costo vs Ganancia (Est)</h3>
                <p class="text-4xl font-black text-green-400">65%</p>
                <p class="text-xs font-bold mt-4">Margen de ganancia bruto</p>
            </div>
            
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-black text-gray-900">Últimas Transacciones</h2>
            </div>
            
            <div class="overflow-x-auto pb-2 -mb-2">
            <table class="w-full text-left min-w-[700px] whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-widest font-bold">
                        <th class="p-4 border-b border-gray-100">Orden #</th>
                        <th class="p-4 border-b border-gray-100">Cliente</th>
                        <th class="p-4 border-b border-gray-100">Tipo</th>
                        <th class="p-4 border-b border-gray-100">Método de Pago</th>
                        <th class="p-4 border-b border-gray-100">Total</th>
                        <th class="p-4 border-b border-gray-100">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-medium text-gray-800">
                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                        <td class="p-4 font-bold text-gray-900">#{{ order.id }}</td>
                        <td class="p-4">{{ order.customer_name || 'Mostrador' }}</td>
                        <td class="p-4">
                            <span :class="order.delivery_type === 'delivery' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'" class="px-2 py-1 rounded text-xs font-bold uppercase">
                                {{ order.delivery_type === 'delivery' ? 'Domicilio' : 'Mesa' }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-gray-500">{{ order.payment_method }}</td>
                        <td class="p-4 font-black text-green-600">${{ parseFloat(order.total).toFixed(2) }}</td>
                        <td class="p-4">
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded uppercase">{{ order.status }}</span>
                        </td>
                    </tr>
                    <tr v-if="recentOrders.length === 0">
                        <td colspan="6" class="p-8 text-center text-gray-400 font-bold">No hay transacciones hoy.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

    </AdminLayout>
</template>
