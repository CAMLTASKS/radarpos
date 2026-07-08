<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    currentSession: Object,
    breakdown: Object,
    ordersCount: Number
});

const openingAmount = ref(0);
const manualCount = ref(0);
const isClosing = ref(false);

const noteForm = ref({
    type: 'ingreso',
    amount: '',
    note: ''
});
const isNoteModalOpen = ref(false);
const isSubmittingNote = ref(false);

const formatPrice = (value) => {
    return Number(value).toLocaleString('es-CO');
};

const elapsedTime = ref('');
let timerInterval = null;

const updateElapsedTime = () => {
    if (props.currentSession && props.currentSession.opened_at) {
        const start = new Date(props.currentSession.opened_at).getTime();
        const now = new Date().getTime();
        const diff = now - start;
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        elapsedTime.value = `${hours}h ${minutes}m`;
    }
};

import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    updateElapsedTime();
    timerInterval = setInterval(updateElapsedTime, 60000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const openRegister = async () => {
    try {
        await axios.post('/api/cash-registers/open', { opening_amount: openingAmount.value });
        router.reload();
    } catch (e) {
        alert(e.response?.data?.error || 'Error al abrir caja');
    }
};

const closeRegister = async () => {
    try {
        await axios.post('/api/cash-registers/close', { closing_amount: manualCount.value });
        window.toast("Caja cerrada correctamente");
        router.reload();
    } catch (e) {
        window.toast(e.response?.data?.error || 'Error al cerrar caja', 'error');
    }
};

const saveNote = async () => {
    if(!noteForm.value.note) return window.toast('La nota es obligatoria', 'error');
    isSubmittingNote.value = true;
    try {
        await axios.post('/api/cash-registers/notes', noteForm.value);
        window.toast('Novedad registrada');
        isNoteModalOpen.value = false;
        noteForm.value = { type: 'ingreso', amount: '', note: '' };
        router.reload();
    } catch (e) {
        window.toast('Error al registrar novedad', 'error');
    } finally {
        isSubmittingNote.value = false;
    }
};
</script>

<template>
    <Head title="Control de Caja" />
    <AdminLayout>
        <div class="h-full bg-gray-50 flex items-center justify-center p-6">
            <div class="max-w-2xl w-full bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Control de Caja</h1>
                        <p class="text-gray-500 mt-1 font-medium">Administración de turno y arqueo de caja.</p>
                    </div>
                    <div class="text-4xl bg-yellow-50 w-16 h-16 rounded-2xl flex items-center justify-center">💰</div>
                </div>

                <div v-if="!currentSession" class="space-y-6">
                    <div class="bg-gray-100 text-gray-600 p-5 rounded-2xl font-bold flex items-center border border-gray-200">
                        <span class="text-2xl mr-3">😴</span> La caja está cerrada.
                    </div>
                    
                    <div class="bg-yellow-50 p-6 rounded-3xl border border-yellow-100">
                        <label class="block text-yellow-800 font-bold mb-3 uppercase tracking-widest text-xs">Monto inicial (Base en efectivo)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold text-xl">$</span>
                            <input v-model="openingAmount" type="number" class="w-full rounded-xl border-yellow-200 focus:ring-yellow-400 focus:border-yellow-400 p-4 pl-10 bg-white text-gray-900 font-black text-2xl outline-none transition shadow-sm" />
                        </div>
                    </div>
                    
                    <button @click="openRegister" class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black text-lg py-4 rounded-xl shadow-md transition">
                        Empezar Turno (Abrir Caja)
                    </button>
                </div>

                <div v-else class="space-y-6">
                    <div class="bg-green-50 text-green-700 p-5 rounded-2xl font-bold flex items-center justify-between border border-green-100">
                        <div class="flex items-center">
                            <span class="relative flex h-3 w-3 mr-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            Turno Activo
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-widest opacity-80 mb-1">Abierto hace {{ elapsedTime }}</p>
                            <span class="text-sm font-bold opacity-90">{{ new Date(currentSession.opened_at).toLocaleString() }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Base</p>
                            <p class="text-xl font-black text-gray-900">${{ formatPrice(currentSession.opening_amount) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ventas</p>
                            <p class="text-xl font-black text-green-500">+${{ formatPrice(breakdown?.Total || 0) }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pedidos</p>
                            <p class="text-xl font-black text-gray-900">{{ ordersCount }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Promedio</p>
                            <p class="text-xl font-black text-blue-500">${{ formatPrice(ordersCount > 0 ? (breakdown?.Total / ordersCount) : 0) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="breakdown" class="border border-gray-100 rounded-2xl p-5 bg-white shadow-sm flex-1">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Desglose por método</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm font-bold border-b border-gray-50 pb-2">
                                    <span class="text-gray-600 flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Efectivo</span>
                                    <span class="text-gray-900">${{ formatPrice(breakdown.Efectivo || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold border-b border-gray-50 pb-2">
                                    <span class="text-gray-600 flex items-center"><span class="w-2 h-2 rounded-full bg-purple-500 mr-2"></span> Nequi</span>
                                    <span class="text-gray-900">${{ formatPrice(breakdown.Nequi || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold border-b border-gray-50 pb-2">
                                    <span class="text-gray-600 flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Daviplata</span>
                                    <span class="text-gray-900">${{ formatPrice(breakdown.Daviplata || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm font-bold pb-2">
                                    <span class="text-gray-600 flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span> Tarjeta</span>
                                    <span class="text-gray-900">${{ formatPrice(breakdown.Tarjeta || 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border border-gray-100 rounded-2xl p-5 bg-white shadow-sm flex-1 flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest">Novedades ({{ currentSession.notes?.length || 0 }})</h3>
                                <button @click="isNoteModalOpen = true" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-bold hover:bg-yellow-200">+ Añadir</button>
                            </div>
                            <div class="space-y-2 overflow-y-auto max-h-40 custom-scrollbar pr-2 flex-1">
                                <div v-for="note in currentSession.notes" :key="note.id" class="text-sm p-3 rounded-lg border" :class="note.type === 'ingreso' ? 'bg-green-50 border-green-100' : (note.type === 'retiro' ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-200')">
                                    <div class="flex justify-between font-bold mb-1">
                                        <span :class="note.type === 'ingreso' ? 'text-green-700' : (note.type === 'retiro' ? 'text-red-700' : 'text-gray-700')">
                                            {{ note.type.toUpperCase() }}
                                        </span>
                                        <span v-if="note.amount > 0">${{ formatPrice(note.amount) }}</span>
                                    </div>
                                    <p class="text-gray-600 text-xs">{{ note.note }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">{{ new Date(note.created_at).toLocaleTimeString() }}</p>
                                </div>
                                <p v-if="!currentSession.notes || currentSession.notes.length === 0" class="text-xs text-gray-400 text-center py-4">Sin novedades en este turno.</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="!isClosing">
                        <button @click="isClosing = true" class="w-full bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 font-black text-lg py-4 rounded-xl transition">
                            Realizar Arqueo y Cerrar Caja
                        </button>
                    </div>

                    <div v-else class="bg-red-50 border border-red-100 rounded-3xl p-6 shadow-inner animate-fade-in-down">
                        <h3 class="text-red-700 font-black mb-4">Cierre de Caja</h3>
                        <label class="block text-red-600 font-bold mb-2 text-sm">Efectivo total físico en caja (Billetes y monedas):</label>
                        <div class="relative mb-4">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold text-xl">$</span>
                            <input v-model="manualCount" type="number" class="w-full rounded-xl border-red-200 focus:ring-red-400 focus:border-red-400 p-4 pl-10 bg-white text-gray-900 font-black text-2xl outline-none transition shadow-sm" />
                        </div>
                        <div class="flex gap-4">
                            <button @click="isClosing = false" class="w-1/3 py-3 font-bold text-gray-500 hover:text-gray-700 transition">Cancelar</button>
                            <button @click="closeRegister" class="w-2/3 bg-red-500 hover:bg-red-600 text-white font-black text-lg py-3 rounded-xl shadow-md transition">
                                Confirmar Cierre 🔒
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Novedades -->
                <Teleport to="body">
                <div v-if="isNoteModalOpen" @click.self="isNoteModalOpen = false" @keydown.esc.window="isNoteModalOpen = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
                    <div class="bg-white rounded-t-3xl lg:rounded-[2rem] w-full max-w-md overflow-hidden shadow-2xl animate-slide-up lg:animate-scale-in max-h-[85vh] lg:max-h-none flex flex-col">
                        <div class="p-6 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-xl font-black text-gray-900">Registrar Novedad de Caja</h2>
                        </div>
                        <div class="p-6 overflow-y-auto space-y-4 flex-1">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Tipo de Novedad</label>
                                <select v-model="noteForm.type" class="w-full bg-gray-50 border-0 rounded-xl p-4 focus:ring-2 focus:ring-yellow-400 font-bold">
                                    <option value="ingreso">Ingreso Extra (+)</option>
                                    <option value="retiro">Retiro / Gasto (-)</option>
                                    <option value="observacion">Observación General</option>
                                </select>
                            </div>
                            <div v-if="noteForm.type !== 'observacion'">
                                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Monto ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold">$</span>
                                    <input v-model="noteForm.amount" type="number" class="w-full rounded-xl bg-gray-50 border-0 focus:ring-2 focus:ring-yellow-400 p-4 pl-10 font-black text-lg outline-none" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Descripción / Nota</label>
                                <textarea v-model="noteForm.note" class="w-full rounded-xl bg-gray-50 border-0 focus:ring-2 focus:ring-yellow-400 p-4 font-medium outline-none h-24" placeholder="Ej: Pago proveedor, Base adicional..."></textarea>
                            </div>
                        </div>
                        <div class="p-6 border-t border-gray-100 flex gap-3">
                            <button @click="isNoteModalOpen = false" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancelar</button>
                            <button @click="saveNote" :disabled="isSubmittingNote" class="flex-1 py-3 bg-gray-900 text-white font-black rounded-xl hover:bg-black transition shadow-md disabled:opacity-50">Guardar</button>
                        </div>
                    </div>
                </div>
                </Teleport>

            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-5px); }
    100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.2s ease-out forwards;
}
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
</style>
