<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
    todaySales: Number,
    activeOrders: Number,
    deliveredOrders: Number,
    lowStockAlerts: Number,
    recentFeedback: Array,
    chartData: Object,
    averageOrderTime: Number,
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
        backgroundColor: '#1F2937',
        titleFont: { size: 14, weight: 'bold' },
        bodyFont: { size: 14, weight: 'bold' },
        padding: 12,
        cornerRadius: 8,
        displayColors: false,
    }
  },
  scales: {
    y: {
        beginAtZero: true,
        grid: { color: 'rgba(0, 0, 0, 0.05)' },
        border: { display: false }
    },
    x: {
        grid: { display: false },
        border: { display: false }
    }
  },
  interaction: {
      mode: 'index',
      intersect: false,
  }
}

const dataForChart = {
  labels: props.chartData.labels,
  datasets: [
    {
      label: 'Ventas ($)',
      backgroundColor: (context) => {
          const ctx = context.chart.ctx;
          const gradient = ctx.createLinearGradient(0, 0, 0, 400);
          gradient.addColorStop(0, 'rgba(250, 204, 21, 0.6)');
          gradient.addColorStop(1, 'rgba(250, 204, 21, 0.0)');
          return gradient;
      },
      borderColor: '#F59E0B',
      borderWidth: 4,
      pointBackgroundColor: '#FFFFFF',
      pointBorderColor: '#F59E0B',
      pointBorderWidth: 3,
      pointRadius: 5,
      pointHoverRadius: 8,
      tension: 0.4,
      fill: true,
      data: props.chartData.data
    }
  ]
}

const formatPrice = (value) => Number(value).toLocaleString('es-CO');
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout>
        
        <!-- Premium Header Banner -->
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-6 lg:p-8 mb-8 shadow-xl text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6 overflow-hidden relative">
            <!-- decorative abstract shape -->
            <div class="absolute -right-20 -top-20 opacity-10 pointer-events-none">
                <svg width="300" height="300" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#FFFFFF" d="M44.7,-76.4C58.8,-69.2,71.8,-59.1,81.1,-46.3C90.4,-33.5,96,-18,94.3,-3.3C92.6,11.4,83.6,25.3,73.1,36.5C62.6,47.7,50.6,56.2,37.3,64.2C24,72.2,9.4,79.7,-6.1,81C-21.6,82.3,-38,77.4,-51.7,69.1C-65.4,60.8,-76.4,49.1,-84.3,35.4C-92.2,21.7,-97,6.1,-95.1,-9.1C-93.2,-24.3,-84.6,-39,-73.4,-51.1C-62.2,-63.2,-48.4,-72.7,-34.1,-79.1C-19.8,-85.5,-5,-88.8,9,-86C23,-83.2,30.6,-83.6,44.7,-76.4Z" transform="translate(100 100)" />
                </svg>
            </div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ new Date().toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-black tracking-tight leading-none">Centro de Comando</h1>
            </div>
            
            <Link href="/pos" class="relative z-10 bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-6 py-3 rounded-xl shadow-lg shadow-yellow-500/30 font-black transition transform hover:-translate-y-1 w-full md:w-auto text-center flex items-center justify-center gap-2">
                <span>Abrir POS</span>
                <span class="text-xl">🚀</span>
            </Link>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
            
            <!-- Ventas -->
            <div class="bg-gradient-to-br from-yellow-400 to-amber-500 rounded-3xl p-5 lg:p-6 shadow-xl shadow-yellow-500/20 text-gray-900 relative overflow-hidden group transform hover:-translate-y-1 transition duration-300 col-span-2 lg:col-span-1">
                <div class="absolute -right-4 -bottom-4 opacity-10 text-8xl lg:text-9xl transform group-hover:scale-110 transition duration-500 group-hover:rotate-12">💰</div>
                <h3 class="text-xs font-black uppercase tracking-widest mb-1 opacity-90">Ventas del Día</h3>
                <p class="text-4xl lg:text-5xl font-black tracking-tight mt-2 break-words">${{ formatPrice(todaySales) }}</p>
                <div class="mt-4 lg:mt-6">
                    <Link href="/reports" class="inline-flex items-center text-[10px] lg:text-xs font-bold uppercase tracking-widest bg-gray-900/10 hover:bg-gray-900/20 px-3 py-1.5 rounded-lg transition backdrop-blur-sm">
                        Ver Detalles <span class="ml-1">→</span>
                    </Link>
                </div>
            </div>

            <!-- Pedidos Activos -->
            <div class="bg-white rounded-3xl p-5 lg:p-6 shadow-sm border-2 border-orange-100 relative overflow-hidden group transform hover:-translate-y-1 transition duration-300">
                <div class="absolute top-4 right-4 text-orange-500 bg-orange-50 w-10 h-10 rounded-full flex items-center justify-center text-xl">🔥</div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 mt-8 lg:mt-0">En Preparación</h3>
                <p class="text-4xl font-black text-gray-900 mt-2">{{ activeOrders }}</p>
                <Link href="/deliveries" class="mt-3 lg:mt-4 inline-block text-[10px] lg:text-xs font-bold uppercase tracking-widest text-orange-600 bg-orange-50 hover:bg-orange-100 px-3 py-1.5 rounded-lg transition">Ir a KDS</Link>
            </div>

            <!-- Tiempo Promedio -->
            <div class="bg-white rounded-3xl p-5 lg:p-6 shadow-sm border-2 border-blue-100 relative overflow-hidden group transform hover:-translate-y-1 transition duration-300">
                <div class="absolute top-4 right-4 text-blue-500 bg-blue-50 w-10 h-10 rounded-full flex items-center justify-center text-xl">⏱️</div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 mt-8 lg:mt-0">Tiempo Prom.</h3>
                <div class="flex items-baseline gap-1 mt-2">
                    <p class="text-4xl font-black text-gray-900">{{ averageOrderTime }}</p>
                    <span class="text-sm font-bold text-gray-400">min</span>
                </div>
                <p class="mt-2 text-[10px] font-bold text-gray-400 truncate">Desde pedido a entrega</p>
            </div>

            <!-- Alertas Stock -->
            <div class="bg-white rounded-3xl p-5 lg:p-6 shadow-sm border-2 border-red-100 relative overflow-hidden group transform hover:-translate-y-1 transition duration-300">
                <div class="absolute top-4 right-4 text-red-500 bg-red-50 w-10 h-10 rounded-full flex items-center justify-center text-xl" :class="lowStockAlerts > 0 ? 'animate-pulse' : ''">⚠️</div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 mt-8 lg:mt-0">Alertas Stock</h3>
                <p class="text-4xl font-black text-gray-900 mt-2">{{ lowStockAlerts }}</p>
                <Link href="/inventory" class="mt-3 lg:mt-4 inline-block text-[10px] lg:text-xs font-bold uppercase tracking-widest text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">Ver Bodega</Link>
            </div>
            
        </div>
        
        <!-- Quick Actions Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <Link href="/pos" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-2 hover:bg-gray-50 transition hover:-translate-y-1 active:scale-95 text-center">
                <span class="text-3xl">🛒</span>
                <span class="font-bold text-sm text-gray-800">Nueva Venta</span>
            </Link>
            <Link href="/deliveries" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-2 hover:bg-gray-50 transition hover:-translate-y-1 active:scale-95 text-center">
                <span class="text-3xl">👨‍🍳</span>
                <span class="font-bold text-sm text-gray-800">Cocina KDS</span>
            </Link>
            <Link href="/cash-register" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-2 hover:bg-gray-50 transition hover:-translate-y-1 active:scale-95 text-center">
                <span class="text-3xl">💵</span>
                <span class="font-bold text-sm text-gray-800">Control Caja</span>
            </Link>
            <Link href="/inventory" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-2 hover:bg-gray-50 transition hover:-translate-y-1 active:scale-95 text-center">
                <span class="text-3xl">📦</span>
                <span class="font-bold text-sm text-gray-800">Inventario</span>
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col p-5 lg:p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg lg:text-xl font-black text-gray-900 tracking-tight">Tendencia de Ventas (7 días)</h2>
                    <span class="text-xs lg:text-sm font-bold text-gray-400 bg-gray-50 px-3 py-1 rounded-full">Total Facturado</span>
                </div>
                <div class="h-64 lg:h-80 w-full relative">
                    <Line :data="dataForChart" :options="chartOptions" />
                </div>
            </div>

            <!-- Feedback -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span>⭐</span> Feedback
                    </h2>
                    <Link href="/comments" class="text-xs font-bold text-blue-500 hover:text-blue-700 bg-blue-50 px-3 py-1 rounded-full">Ver todos</Link>
                </div>
                <div class="p-4 lg:p-6 space-y-4 overflow-y-auto max-h-[350px] lg:max-h-[400px] custom-scrollbar">
                    <div v-if="recentFeedback.length === 0" class="text-gray-400 text-sm font-bold text-center py-8">
                        <div class="text-4xl mb-3 opacity-30">😶</div>
                        Aún no hay calificaciones recientes.
                    </div>
                    
                    <div v-for="fb in recentFeedback" :key="fb.id" class="bg-white p-4 rounded-2xl border border-gray-100 hover:border-gray-200 shadow-sm transition">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="font-black text-gray-800 text-sm block">{{ fb.customer_name || 'Cliente anónimo' }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mt-0.5">Orden #{{ fb.id }}</span>
                            </div>
                            <div class="flex text-yellow-400 text-sm drop-shadow-sm bg-yellow-50 px-2 py-1 rounded-lg">
                                <span v-for="n in 5" :key="n" :class="n <= fb.rating ? 'text-yellow-400' : 'text-yellow-200'">★</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 italic bg-gray-50 p-3 rounded-xl border border-gray-100 relative">
                            <span class="absolute -top-2 -left-2 text-xl opacity-20">"</span>
                            {{ fb.feedback_comments || 'Sin comentarios adicionales.' }}
                        </p>
                        <p class="text-[9px] text-gray-400 mt-3 font-bold uppercase tracking-wider text-right">{{ new Date(fb.updated_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f9fafb;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #d1d5db; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #9ca3af;
}
</style>
