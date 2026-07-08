<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

const props = defineProps({
    orders: Array,
});

// Calculate statistics
const averageRating = computed(() => {
    if (props.orders.length === 0) return 0;
    const sum = props.orders.reduce((acc, order) => acc + (order.rating || 0), 0);
    return (sum / props.orders.length).toFixed(1);
});

const totalReviews = computed(() => props.orders.length);

const ratingCounts = computed(() => {
    const counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
    props.orders.forEach(order => {
        if (counts[order.rating] !== undefined) {
            counts[order.rating]++;
        }
    });
    return Object.values(counts);
});

// Chart Data
const barChartData = computed(() => ({
    labels: ['1 Estrella', '2 Estrellas', '3 Estrellas', '4 Estrellas', '5 Estrellas'],
    datasets: [{
        label: 'Cantidad de Calificaciones',
        backgroundColor: ['#EF4444', '#F97316', '#F59E0B', '#84CC16', '#22C55E'],
        borderRadius: 8,
        data: ratingCounts.value
    }]
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
    }
};

const waitTimeData = computed(() => {
    let under15 = 0;
    let between15and30 = 0;
    let over30 = 0;

    props.orders.forEach(order => {
        const start = new Date(order.created_at);
        const end = new Date(order.updated_at);
        const diffMins = (end - start) / (1000 * 60);

        if (diffMins < 15) under15++;
        else if (diffMins <= 30) between15and30++;
        else over30++;
    });

    return {
        labels: ['Menos de 15m', '15m - 30m', 'Más de 30m'],
        datasets: [{
            backgroundColor: ['#22C55E', '#F59E0B', '#EF4444'],
            borderWidth: 0,
            data: [under15, between15and30, over30]
        }]
    };
});

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' }
    }
};

</script>

<template>
    <Head title="Feedback y Comentarios" />
    <AdminLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Voz del Cliente 🗣️</h1>
            <p class="text-gray-500 font-medium mt-1">Análisis de satisfacción y comentarios de clientes.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-3xl p-6 shadow-lg shadow-yellow-500/20 text-gray-900 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest opacity-80 mb-1">Calificación Promedio</p>
                    <div class="flex items-end gap-2">
                        <span class="text-5xl font-black">{{ averageRating }}</span>
                        <span class="text-lg font-bold mb-1 opacity-75">/ 5</span>
                    </div>
                </div>
                <div class="text-6xl opacity-50">⭐</div>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total Reseñas</p>
                    <span class="text-5xl font-black text-gray-900">{{ totalReviews }}</span>
                </div>
                <div class="text-6xl opacity-20">📝</div>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Satisfacción</p>
                    <span class="text-3xl font-black" :class="averageRating >= 4 ? 'text-green-500' : (averageRating >= 3 ? 'text-yellow-500' : 'text-red-500')">
                        {{ averageRating >= 4 ? 'Excelente 🎉' : (averageRating >= 3 ? 'Regular 😐' : 'Deficiente 🚨') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-black text-gray-900 mb-6">Distribución de Calificaciones</h3>
                <div class="h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-black text-gray-900 mb-6">Tiempos de Espera (Desempeño)</h3>
                <div class="h-64 flex justify-center">
                    <Pie :data="waitTimeData" :options="pieChartOptions" class="w-full max-w-[300px]" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-black text-gray-900">Historial de Comentarios</h3>
            </div>
            
            <div class="divide-y divide-gray-100">
                <div v-for="order in orders" :key="order.id" class="p-6 hover:bg-gray-50 transition flex flex-col md:flex-row gap-4">
                    <div class="flex-shrink-0 w-32">
                        <span class="text-sm font-black text-gray-500">#{{ order.id }}</span>
                        <div class="flex text-yellow-400 text-sm mt-1">
                            <span v-for="n in 5" :key="n" :class="n <= order.rating ? 'text-yellow-400' : 'text-gray-200'">★</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 font-bold">{{ new Date(order.updated_at).toLocaleDateString() }}</p>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-900 text-sm mb-1">{{ order.customer_name || 'Cliente Anónimo' }} <span class="text-gray-400 font-normal ml-2">{{ order.customer_phone || '' }}</span></p>
                        <p class="text-gray-700 italic bg-gray-100 p-4 rounded-xl text-sm border border-gray-200">
                            "{{ order.feedback_comments || 'Sin comentarios adicionales.' }}"
                        </p>
                    </div>
                </div>
                
                <div v-if="orders.length === 0" class="p-12 text-center">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-lg font-bold text-gray-900">Sin reseñas</h3>
                    <p class="text-gray-500 text-sm">Aún no hay calificaciones de clientes.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
