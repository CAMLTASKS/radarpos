<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    orders: Array
});

const startDate = ref('');
const endDate = ref('');
const statusFilter = ref('');
const paymentFilter = ref('');

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        let matches = true;
        
        if (statusFilter.value && order.status !== statusFilter.value) {
            matches = false;
        }

        if (paymentFilter.value && order.payment_method !== paymentFilter.value) {
            matches = false;
        }
        
        if (startDate.value) {
            const orderDate = new Date(order.created_at).toISOString().split('T')[0];
            if (orderDate < startDate.value) matches = false;
        }
        
        if (endDate.value) {
            const orderDate = new Date(order.created_at).toISOString().split('T')[0];
            if (orderDate > endDate.value) matches = false;
        }
        
        return matches;
    });
});

const totalSales = computed(() => {
    return filteredOrders.value
        .filter(o => o.status === 'completed')
        .reduce((sum, order) => sum + parseFloat(order.total), 0);
});

const totalOrdersCount = computed(() => filteredOrders.value.length);

const formatPrice = (value) => Number(value).toLocaleString('es-CO');

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        preparing: 'Preparando',
        ready: 'Listo',
        on_way: 'En Camino',
        completed: 'Completado',
        cancelled: 'Cancelado'
    };
    return labels[status] || status;
};

const exportCSV = () => {
    if (filteredOrders.value.length === 0) return window.toast('No hay datos para exportar', 'error');

    let csvContent = "ID Orden,Fecha,Cliente,Telefono,Tipo,Metodo Pago,Estado,Total\n";
    
    filteredOrders.value.forEach(order => {
        const id = order.id;
        const date = new Date(order.created_at).toLocaleString();
        const customer = (order.customer_name || 'Mostrador').replace(/,/g, '');
        const phone = (order.customer_phone || '').replace(/,/g, '');
        const type = order.type || 'local';
        const payment = order.payment_method || 'Efectivo';
        const status = getStatusLabel(order.status);
        const total = order.total;

        csvContent += `${id},"${date}","${customer}","${phone}",${type},${payment},${status},${total}\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `reporte_ventas_${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

</script>

<template>
    <Head title="Reportes Financieros" />
    <AdminLayout>
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Reportes Financieros</h1>
                <p class="text-gray-500 font-medium">Filtra y exporta el historial de ventas.</p>
            </div>
            <button @click="exportCSV" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-xl shadow-md transition flex items-center gap-2">
                <span>📊</span> Exportar CSV
            </button>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8 flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha Inicio</label>
                <input v-model="startDate" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Fecha Fin</label>
                <input v-model="endDate" type="date" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Estado</label>
                <select v-model="statusFilter" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none">
                    <option value="">Todos los Estados</option>
                    <option value="completed">Completados (Facturado)</option>
                    <option value="cancelled">Cancelados</option>
                    <option value="pending">Pendientes</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Método de Pago</label>
                <select v-model="paymentFilter" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none">
                    <option value="">Todos</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Nequi">Nequi</option>
                    <option value="Daviplata">Daviplata</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-900 text-white p-6 rounded-3xl shadow-lg border border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Facturado (Filtro)</p>
                    <p class="text-4xl font-black text-yellow-400">${{ formatPrice(totalSales) }}</p>
                </div>
                <div class="text-6xl opacity-20">💰</div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Órdenes</p>
                    <p class="text-4xl font-black text-gray-900">{{ totalOrdersCount }}</p>
                </div>
                <div class="text-6xl opacity-20">📋</div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col max-h-[60vh]">
            <div class="overflow-auto custom-scrollbar flex-1">
                <table class="w-full text-left border-collapse relative">
                    <thead class="sticky top-0 bg-gray-50 z-10 shadow-sm">
                        <tr class="border-b border-gray-100">
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest">ID</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest">Fecha</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest">Cliente</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest">Método</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest">Estado</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-widest text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 transition">
                            <td class="p-4 font-black text-gray-900">#{{ order.id }}</td>
                            <td class="p-4 text-gray-600 text-sm">{{ new Date(order.created_at).toLocaleString() }}</td>
                            <td class="p-4 font-bold text-gray-800 text-sm">
                                {{ order.customer_name || 'Mostrador' }}
                                <span v-if="order.type === 'domicilio'" class="ml-2 bg-purple-100 text-purple-700 text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-widest">Dom</span>
                            </td>
                            <td class="p-4 font-bold text-gray-600 text-sm">{{ order.payment_method || 'Efectivo' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest"
                                    :class="{
                                        'bg-yellow-100 text-yellow-700': order.status === 'pending',
                                        'bg-green-100 text-green-700': order.status === 'completed',
                                        'bg-red-100 text-red-700': order.status === 'cancelled',
                                        'bg-blue-100 text-blue-700': !['pending', 'completed', 'cancelled'].includes(order.status)
                                    }">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-black text-gray-900">${{ formatPrice(order.total) }}</td>
                        </tr>
                        <tr v-if="filteredOrders.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-500">No se encontraron órdenes con estos filtros.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #D1D5DB; 
  border-radius: 20px;
}
</style>
