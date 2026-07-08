<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const tables = ref(Array.from({ length: 12 }, (_, i) => ({
    id: i + 1,
    number: i + 1,
    status: Math.random() > 0.7 ? 'occupied' : 'free',
    guests: Math.floor(Math.random() * 4) + 1
})));

</script>

<template>
    <Head title="Mesas" />
    <AdminLayout>
        
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Mapa de Mesas</h1>
            <div class="flex space-x-3 text-sm font-bold">
                <span class="flex items-center"><div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div> Libre</span>
                <span class="flex items-center"><div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div> Ocupada</span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <Link href="/pos" v-for="table in tables" :key="table.id" class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center hover:shadow-xl transition transform hover:-translate-y-1 relative group cursor-pointer h-48">
                
                <div class="absolute inset-0 bg-gray-50 rounded-3xl opacity-0 group-hover:opacity-100 transition -z-10"></div>
                
                <!-- Table Graphic -->
                <div class="w-24 h-24 rounded-full border-8 mb-3 flex items-center justify-center relative transition-colors duration-300" :class="table.status === 'occupied' ? 'border-red-100 bg-red-50 text-red-500' : 'border-green-100 bg-green-50 text-green-500'">
                    <span class="text-2xl font-black">{{ table.number }}</span>
                    
                    <!-- Chairs -->
                    <div class="absolute -top-3 w-6 h-3 rounded-t-full" :class="table.status === 'occupied' ? 'bg-red-200' : 'bg-green-200'"></div>
                    <div class="absolute -bottom-3 w-6 h-3 rounded-b-full" :class="table.status === 'occupied' ? 'bg-red-200' : 'bg-green-200'"></div>
                    <div class="absolute -left-3 w-3 h-6 rounded-l-full" :class="table.status === 'occupied' ? 'bg-red-200' : 'bg-green-200'"></div>
                    <div class="absolute -right-3 w-3 h-6 rounded-r-full" :class="table.status === 'occupied' ? 'bg-red-200' : 'bg-green-200'"></div>
                </div>

                <p class="font-bold text-gray-800 text-sm">
                    {{ table.status === 'occupied' ? 'Ocupada (' + table.guests + ' pers.)' : 'Disponible' }}
                </p>
                <p class="text-xs text-gray-400 font-medium mt-1">Clic para abrir POS</p>
            </Link>
        </div>

    </AdminLayout>
</template>
