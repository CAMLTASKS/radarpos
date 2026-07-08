<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    ingredients: Array
});

const searchQuery = ref('');

const filteredIngredients = computed(() => {
    return props.ingredients.filter(item => {
        return item.name.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

const form = ref({
    name: '',
    stock: 0,
    unit: 'Gramos',
    cost: 0
});

const isModalOpen = ref(false);
const editingId = ref(null);

const totalInventoryValue = computed(() => {
    return props.ingredients.reduce((total, item) => total + (item.stock * item.cost), 0);
});

const lowStockItems = computed(() => {
    return props.ingredients.filter(item => item.stock < 100);
});

const openCreateModal = () => {
    editingId.value = null;
    form.value = { name: '', stock: 0, unit: 'Gramos', cost: 0 };
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingId.value = item.id;
    form.value = { name: item.name, stock: item.stock, unit: item.unit, cost: item.cost };
    isModalOpen.value = true;
};

const saveIngredient = async () => {
    try {
        if (editingId.value) {
            await axios.put(`/api/ingredients/${editingId.value}`, form.value);
            window.toast("Insumo actualizado correctamente");
        } else {
            await axios.post('/api/ingredients', form.value);
            window.toast("Insumo creado correctamente");
        }
        isModalOpen.value = false;
        router.reload();
    } catch (e) {
        window.toast('Error al guardar insumo', 'error');
    }
};

const deleteIngredient = async (id) => {
    if (confirm("¿Estás seguro de inhabilitar este insumo?")) {
        try {
            await axios.delete(`/api/ingredients/${id}`);
            window.toast("Insumo inhabilitado");
            isModalOpen.value = false;
            router.reload();
        } catch (e) {
            window.toast("Error al inhabilitar", "error");
        }
    }
};
</script>

<template>
    <Head title="Inventario" />
    <AdminLayout>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Bodega de Insumos</h1>
                <p class="text-gray-500 font-medium mt-1">Control de inventario, stock y costos de la receta.</p>
            </div>
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">🔍</span>
                    <input v-model="searchQuery" type="text" placeholder="Buscar insumo..." class="w-full bg-white border border-gray-200 rounded-xl py-2 pl-10 pr-4 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition shadow-sm font-medium">
                </div>
                <button @click="openCreateModal" class="whitespace-nowrap bg-gray-900 hover:bg-black text-white font-bold px-6 py-2 rounded-xl shadow-lg transition transform hover:-translate-y-1">
                    + Ingresar Insumo
                </button>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-6 shadow-sm border border-indigo-200 relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">📦</div>
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-1">Total Insumos</p>
                <p class="text-4xl font-black text-indigo-900">{{ ingredients.length }}</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 shadow-sm border border-green-200 relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">💰</div>
                <p class="text-xs font-bold text-green-600 uppercase tracking-widest mb-1">Valor Total Inventario</p>
                <p class="text-4xl font-black text-green-900">${{ totalInventoryValue.toFixed(2) }}</p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 shadow-sm border border-red-200 relative overflow-hidden">
                <div class="absolute right-[-1rem] top-[-1rem] text-8xl opacity-10">⚠️</div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-widest mb-1">Alertas (Stock Bajo)</p>
                <p class="text-4xl font-black text-red-700">{{ lowStockItems.length }}</p>
            </div>
        </div>

        <!-- Tabla Moderna -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-20 lg:mb-0">
            <div class="overflow-x-auto pb-2 -mb-2">
                <table class="w-full text-left border-collapse min-w-[700px] whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-widest font-bold">
                            <th class="p-5 border-b border-gray-100">Insumo</th>
                            <th class="p-5 border-b border-gray-100 text-center">Stock Actual</th>
                            <th class="p-5 border-b border-gray-100 text-right">Costo Unitario</th>
                            <th class="p-5 border-b border-gray-100 text-right">Valor Total en Bodega</th>
                            <th class="p-5 border-b border-gray-100 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium text-gray-800">
                        <tr v-for="item in filteredIngredients" :key="item.id" class="hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                            <td class="p-5">
                                <div class="flex items-center space-x-4">
                                    <span class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-xl shadow-sm border border-gray-200">🥣</span>
                                    <span class="font-bold text-gray-900 text-base">{{ item.name }}</span>
                                </div>
                            </td>
                            <td class="p-5 text-center">
                                <span :class="item.stock < 100 ? 'text-red-700 bg-red-100 border-red-200' : 'text-gray-700 bg-gray-100 border-gray-200'" class="px-3 py-1.5 rounded-lg font-bold border shadow-sm inline-block min-w-[80px]">
                                    {{ item.stock }} {{ item.unit }}
                                </span>
                            </td>
                            <td class="p-5 text-right font-bold text-gray-500">${{ parseFloat(item.cost).toFixed(2) }}</td>
                            <td class="p-5 text-right font-black text-gray-900 text-lg">${{ (item.stock * item.cost).toFixed(2) }}</td>
                            <td class="p-5 text-center flex justify-center space-x-2">
                                <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl transition shadow-sm border border-blue-100">✏️</button>
                                <button @click="deleteIngredient(item.id)" class="text-red-600 hover:text-red-800 font-bold bg-red-50 hover:bg-red-100 px-3 py-2 rounded-xl transition shadow-sm border border-red-100">🗑️</button>
                            </td>
                        </tr>
                        <tr v-if="filteredIngredients.length === 0">
                            <td colspan="5" class="p-12 text-center">
                                <div class="text-6xl mb-4">🔍</div>
                                <p class="text-gray-400 font-bold text-lg">No se encontraron insumos.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" @click.self="isModalOpen = false" @keydown.esc.window="isModalOpen = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
            <div class="bg-white rounded-t-3xl lg:rounded-[2rem] w-full max-w-md overflow-hidden shadow-2xl animate-slide-up lg:animate-scale-in max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-100 bg-gray-50 text-center">
                    <div class="text-4xl mb-2">📦</div>
                    <h2 class="text-2xl font-black text-gray-900">{{ editingId ? 'Actualizar Lote' : 'Ingresar Insumo' }}</h2>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Nombre del Insumo</label>
                        <input v-model="form.name" type="text" class="w-full bg-gray-50 border-0 rounded-xl p-4 focus:ring-2 focus:ring-yellow-400 font-bold">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Cantidad (Stock)</label>
                            <input v-model="form.stock" type="number" step="0.01" class="w-full bg-gray-50 border-0 rounded-xl p-4 focus:ring-2 focus:ring-yellow-400 font-black text-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Unidad de Medida</label>
                            <select v-model="form.unit" class="w-full bg-gray-50 border-0 rounded-xl p-4 focus:ring-2 focus:ring-yellow-400 font-bold">
                                <option>Gramos</option>
                                <option>Litros</option>
                                <option>Unidades</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Costo Unitario ($) - Valor Real</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold">$</span>
                            <input v-model="form.cost" type="number" step="0.01" class="w-full bg-gray-50 border-0 rounded-xl p-4 pl-10 focus:ring-2 focus:ring-yellow-400 font-black text-lg">
                        </div>
                        <p class="text-xs text-gray-400 mt-2 font-medium">Este valor se usará para calcular el escandallo (rentabilidad) de las recetas.</p>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 flex space-x-3 bg-white">
                    <button @click="isModalOpen = false" class="flex-1 py-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancelar</button>
                    <button @click="saveIngredient" class="flex-1 py-4 bg-gray-900 text-white font-black rounded-xl hover:bg-black transition shadow-md">Guardar Insumo</button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
@keyframes scale-in {
    0% { opacity: 0; transform: scale(0.95); }
    100% { opacity: 1; transform: scale(1); }
}
.animate-scale-in {
    animation: scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slide-up {
    0% { transform: translateY(100%); }
    100% { transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #D1D5DB; border-radius: 20px; }
</style>
