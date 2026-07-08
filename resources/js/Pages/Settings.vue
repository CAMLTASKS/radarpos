<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Array,
    drivers: Array,
    promotions: Array,
    products: Array,
});

const activeTab = ref('categories'); // 'drivers', 'params', 'categories', 'promos'

// Load categories from settings
const catSetting = props.settings.find(s => s.key_name === 'categories');
const initialCategories = catSetting ? catSetting.value.split(',').map(s => s.trim()).filter(s => s) : ['Limonadas', 'Salpicón', 'Waffles', 'Crepes', 'Obleas'];
const categories = ref(initialCategories);
const newCategory = ref('');

const addCategory = () => {
    if (newCategory.value && !categories.value.includes(newCategory.value)) {
        categories.value.push(newCategory.value.trim());
        newCategory.value = '';
    }
};

const removeCategory = (index) => {
    categories.value.splice(index, 1);
};

const saveCategories = () => {
    router.post('/settings/categories', { categories: categories.value }, {
        onSuccess: () => {
            window.toast('Categorías actualizadas');
        },
        onError: () => {
            window.toast('Error al guardar categorías', 'error');
        }
    });
};

// Forms
const driverForm = useForm({
    id: null,
    name: '',
    phone: '',
    vehicle_plate: '',
    is_active: true
});

const settingForm = useForm({
    id: null,
    key_name: '',
    value: '',
    description: ''
});

// Modals state
const isEditingDriver = ref(false);
const showDriverModal = ref(false);

const isEditingSetting = ref(false);
const showSettingModal = ref(false);

const promoForm = useForm({
    id: null,
    name: '',
    type: 'percentage', // percentage, fixed, free_product
    value: 0,
    free_product_id: null,
    is_active: true
});

const isEditingPromo = ref(false);
const showPromoModal = ref(false);

// Driver Handlers
const openCreateDriver = () => {
    isEditingDriver.value = false;
    driverForm.reset();
    driverForm.clearErrors();
    showDriverModal.value = true;
};

const openEditDriver = (driver) => {
    isEditingDriver.value = true;
    driverForm.clearErrors();
    driverForm.id = driver.id;
    driverForm.name = driver.name;
    driverForm.phone = driver.phone;
    driverForm.vehicle_plate = driver.vehicle_plate;
    driverForm.is_active = driver.is_active;
    showDriverModal.value = true;
};

const closeDriverModal = () => { showDriverModal.value = false; driverForm.reset(); };

const saveDriver = () => {
    if (isEditingDriver.value) {
        driverForm.put(`/drivers/${driverForm.id}`, { onSuccess: closeDriverModal });
    } else {
        driverForm.post('/drivers', { onSuccess: closeDriverModal });
    }
};

const deleteDriver = (id) => {
    if (confirm('¿Eliminar repartidor?')) {
        router.delete(`/drivers/${id}`);
    }
};

// Setting Handlers
const openCreateSetting = () => {
    isEditingSetting.value = false;
    settingForm.reset();
    settingForm.clearErrors();
    showSettingModal.value = true;
};

const openEditSetting = (setting) => {
    isEditingSetting.value = true;
    settingForm.clearErrors();
    settingForm.id = setting.id;
    settingForm.key_name = setting.key_name;
    settingForm.value = setting.value;
    settingForm.description = setting.description;
    showSettingModal.value = true;
};

const closeSettingModal = () => { showSettingModal.value = false; settingForm.reset(); };

const saveSetting = () => {
    if (isEditingSetting.value) {
        settingForm.put(`/settings/${settingForm.id}`, { onSuccess: closeSettingModal });
    } else {
        settingForm.post('/settings', { onSuccess: closeSettingModal });
    }
};

const deleteSetting = (id) => {
    if (confirm('¿Eliminar parámetro?')) {
        router.delete(`/settings/${id}`);
    }
};

// Promos Handlers
const openCreatePromo = () => {
    isEditingPromo.value = false;
    promoForm.reset();
    promoForm.clearErrors();
    showPromoModal.value = true;
};

const openEditPromo = (promo) => {
    isEditingPromo.value = true;
    promoForm.clearErrors();
    promoForm.id = promo.id;
    promoForm.name = promo.name;
    promoForm.type = promo.type;
    promoForm.value = promo.value;
    promoForm.free_product_id = promo.free_product_id;
    promoForm.is_active = promo.is_active;
    showPromoModal.value = true;
};

const closePromoModal = () => { showPromoModal.value = false; promoForm.reset(); };

const savePromo = () => {
    if (isEditingPromo.value) {
        promoForm.put(`/api/promotions/${promoForm.id}`, { 
            onSuccess: () => { closePromoModal(); window.toast('Promoción actualizada'); } 
        });
    } else {
        promoForm.post('/api/promotions', { 
            onSuccess: () => { closePromoModal(); window.toast('Promoción creada'); } 
        });
    }
};

const deletePromo = (id) => {
    if (confirm('¿Eliminar promoción?')) {
        router.delete(`/api/promotions/${id}`, {
            onSuccess: () => { window.toast('Promoción eliminada'); }
        });
    }
};
</script>

<template>
    <Head title="Configuración" />
    <AdminLayout>
        
        <!-- Premium Header Banner -->
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-6 lg:p-8 mb-8 shadow-xl text-white relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="absolute -right-10 -top-10 opacity-10 pointer-events-none text-9xl">⚙️</div>
            <div class="relative z-10 w-full">
                <h1 class="text-3xl lg:text-4xl font-black tracking-tight flex items-center gap-3">
                    <span class="text-yellow-400">⚙️</span> Configuración del Sistema
                </h1>
                <p class="text-gray-400 mt-2 font-bold text-sm lg:text-base">Ajusta los parámetros generales, categorías y equipo de tu negocio.</p>
            </div>
        </div>
        
        <!-- Tabs Pills -->
        <div class="flex overflow-x-auto pb-4 mb-6 custom-scrollbar gap-2 hide-scroll-if-possible">
            <button @click="activeTab = 'categories'" class="flex-shrink-0 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-sm border" :class="activeTab === 'categories' ? 'bg-yellow-400 text-gray-900 border-yellow-400' : 'bg-white text-gray-500 hover:text-gray-800 border-gray-200'">📦 Categorías</button>
            <button @click="activeTab = 'promos'" class="flex-shrink-0 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-sm border" :class="activeTab === 'promos' ? 'bg-yellow-400 text-gray-900 border-yellow-400' : 'bg-white text-gray-500 hover:text-gray-800 border-gray-200'">🎁 Promociones</button>
            <button @click="activeTab = 'drivers'" class="flex-shrink-0 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-sm border" :class="activeTab === 'drivers' ? 'bg-yellow-400 text-gray-900 border-yellow-400' : 'bg-white text-gray-500 hover:text-gray-800 border-gray-200'">🛵 Repartidores</button>
            <button @click="activeTab = 'params'" class="flex-shrink-0 px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-sm border" :class="activeTab === 'params' ? 'bg-yellow-400 text-gray-900 border-yellow-400' : 'bg-white text-gray-500 hover:text-gray-800 border-gray-200'">🔧 Avanzado</button>
        </div>

        <!-- Tab: Categorías -->
        <div v-if="activeTab === 'categories'" class="bg-white p-6 lg:p-8 rounded-3xl shadow-sm border border-gray-100 animate-scale-in">
            <h2 class="text-xl font-black text-gray-900 mb-2">Administrar Categorías</h2>
            <p class="text-sm text-gray-500 font-bold mb-6">Agrega o elimina las categorías del POS.</p>
            
            <div class="flex flex-col md:flex-row gap-3 mb-8 w-full md:max-w-xl">
                <input v-model="newCategory" @keyup.enter="addCategory" type="text" placeholder="Nueva categoría..." class="flex-1 bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none font-bold">
                <button @click="addCategory" class="bg-gray-900 hover:bg-black text-white px-6 py-3 font-bold rounded-xl transition shadow-md w-full md:w-auto">Agregar</button>
            </div>

            <div class="flex flex-wrap gap-3 mb-8">
                <div v-for="(cat, index) in categories" :key="index" class="bg-yellow-50 border border-yellow-200 text-yellow-800 font-black px-4 py-2 rounded-xl flex items-center gap-2 shadow-sm">
                    {{ cat }}
                    <button @click="removeCategory(index)" class="w-6 h-6 bg-white text-yellow-600 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition shadow-sm text-sm ml-1">&times;</button>
                </div>
                <div v-if="categories.length === 0" class="text-gray-400 text-sm font-bold italic w-full p-4 text-center bg-gray-50 rounded-xl">No hay categorías.</div>
            </div>

            <button @click="saveCategories" class="w-full md:w-auto bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-black px-8 py-3.5 rounded-xl transition shadow-lg text-lg text-center">Guardar Categorías</button>
        </div>

        <!-- Tab: Promociones -->
        <div v-if="activeTab === 'promos'" class="animate-scale-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 class="text-xl font-black text-gray-900">🎁 Promociones y Descuentos</h2>
                <button @click="openCreatePromo" class="w-full md:w-auto bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-5 py-2.5 rounded-xl font-black transition shadow-sm text-sm flex items-center justify-center gap-2">
                    <span class="text-lg leading-none">+</span> Nueva Promoción
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="promo in promotions" :key="promo.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-2">
                        <span v-if="promo.is_active" class="bg-green-100 text-green-700 text-[10px] font-black uppercase px-2 py-1 rounded-lg">Activa</span>
                        <span v-else class="bg-red-100 text-red-700 text-[10px] font-black uppercase px-2 py-1 rounded-lg">Inactiva</span>
                    </div>
                    
                    <h3 class="font-black text-gray-900 text-lg mb-1 pr-16">{{ promo.name }}</h3>
                    
                    <div class="mt-2 mb-4">
                        <span v-if="promo.type === 'percentage'" class="bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-xl text-sm font-bold shadow-sm inline-flex items-center gap-1">
                            <span>📉</span> -{{ promo.value }}%
                        </span>
                        <span v-else-if="promo.type === 'fixed'" class="bg-orange-50 text-orange-700 border border-orange-200 px-3 py-1.5 rounded-xl text-sm font-bold shadow-sm inline-flex items-center gap-1">
                            <span>💵</span> -${{ Number(promo.value).toLocaleString() }}
                        </span>
                        <span v-else-if="promo.type === 'free_product'" class="bg-purple-50 text-purple-700 border border-purple-200 px-3 py-1.5 rounded-xl text-sm font-bold shadow-sm inline-flex flex-col gap-1">
                            <div class="flex items-center gap-1"><span>🎁</span> Producto Gratis</div>
                            <span v-if="promo.free_product" class="text-xs opacity-80 pl-5 text-gray-600 block truncate max-w-xs">{{ promo.free_product.name }}</span>
                        </span>
                    </div>
                    
                    <div class="mt-auto pt-4 flex gap-2 border-t border-gray-50">
                        <button @click="openEditPromo(promo)" class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 py-2 rounded-xl text-xs font-black transition">Editar</button>
                        <button @click="deletePromo(promo.id)" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-xl text-xs font-black transition">Eliminar</button>
                    </div>
                </div>
            </div>
            
            <div v-if="!promotions || promotions.length === 0" class="bg-white p-8 rounded-3xl border border-gray-100 text-center shadow-sm">
                <div class="text-4xl mb-2 opacity-50">🎁</div>
                <p class="text-gray-500 font-bold">Aún no hay promociones configuradas.</p>
            </div>
        </div>

        <!-- Tab: Repartidores -->
        <div v-if="activeTab === 'drivers'" class="animate-scale-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 class="text-xl font-black text-gray-900">🛵 Directorio de Repartidores</h2>
                <button @click="openCreateDriver" class="w-full md:w-auto bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-5 py-2.5 rounded-xl font-black transition shadow-sm text-sm flex items-center justify-center gap-2">
                    <span class="text-lg leading-none">+</span> Nuevo Repartidor
                </button>
            </div>

            <!-- Mobile: Cards view -->
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                <div v-for="driver in drivers" :key="driver.id" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-2 relative">
                    <div class="absolute top-4 right-4">
                        <span :class="driver.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-lg text-[10px] font-black uppercase">
                            {{ driver.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="font-black text-gray-900 text-lg">{{ driver.name }}</div>
                    <div class="text-sm font-bold text-gray-500 flex items-center gap-2"><span>📱</span> {{ driver.phone || 'Sin teléfono' }}</div>
                    <div class="text-sm font-bold text-gray-500 flex items-center gap-2"><span>🏍️</span> {{ driver.vehicle_plate || 'N/A' }}</div>
                    <div class="flex gap-2 mt-2 pt-3 border-t border-gray-50">
                        <button @click="openEditDriver(driver)" class="flex-1 bg-gray-50 py-2 rounded-xl text-xs font-black text-gray-700">Editar</button>
                        <button @click="deleteDriver(driver.id)" class="flex-1 bg-red-50 py-2 rounded-xl text-xs font-black text-red-600">Eliminar</button>
                    </div>
                </div>
            </div>

            <!-- Desktop: Table view -->
            <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Nombre</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Teléfono</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Placa</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Estado</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="driver in drivers" :key="driver.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="py-4 px-6 font-black text-gray-900">{{ driver.name }}</td>
                            <td class="py-4 px-6 text-sm font-bold text-gray-500">{{ driver.phone }}</td>
                            <td class="py-4 px-6"><span class="bg-gray-100 px-3 py-1 rounded-lg text-xs font-black text-gray-600">{{ driver.vehicle_plate || 'N/A' }}</span></td>
                            <td class="py-4 px-6">
                                <span :class="driver.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-3 py-1 rounded-lg text-xs font-black">
                                    {{ driver.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button @click="openEditDriver(driver)" class="text-blue-600 hover:text-blue-800 font-black text-sm mx-2 px-2 py-1 bg-blue-50 rounded-lg">Editar</button>
                                <button @click="deleteDriver(driver.id)" class="text-red-600 hover:text-red-800 font-black text-sm mx-2 px-2 py-1 bg-red-50 rounded-lg">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!drivers || drivers.length === 0" class="bg-white p-8 rounded-3xl border border-gray-100 text-center shadow-sm lg:hidden mt-4">
                <p class="text-gray-500 font-bold">No hay repartidores.</p>
            </div>
        </div>

        <!-- Tab: Parámetros -->
        <div v-if="activeTab === 'params'" class="animate-scale-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 class="text-xl font-black text-gray-900">🔧 Parámetros Avanzados</h2>
                <button @click="openCreateSetting" class="w-full md:w-auto bg-gray-900 hover:bg-black text-white px-5 py-2.5 rounded-xl font-black transition shadow-sm text-sm flex items-center justify-center gap-2">
                    <span class="text-lg leading-none">+</span> Nuevo Parámetro
                </button>
            </div>

            <!-- Mobile cards -->
            <div class="grid grid-cols-1 gap-4 lg:hidden">
                <div v-for="setting in settings" :key="setting.id" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-2">
                    <div class="font-black text-gray-900">{{ setting.key_name }}</div>
                    <div class="text-sm font-bold text-gray-700 bg-gray-50 p-2 rounded-lg break-all">{{ setting.value || 'N/A' }}</div>
                    <div class="text-xs font-bold text-gray-400 mt-1">{{ setting.description || 'Sin descripción' }}</div>
                    <div class="flex gap-2 mt-2 pt-3 border-t border-gray-50">
                        <button @click="openEditSetting(setting)" class="flex-1 bg-gray-50 py-2 rounded-xl text-xs font-black text-gray-700">Editar</button>
                        <button @click="deleteSetting(setting.id)" class="flex-1 bg-red-50 py-2 rounded-xl text-xs font-black text-red-600">Eliminar</button>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Key (Clave)</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Valor</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest">Descripción</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="setting in settings" :key="setting.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="py-4 px-6 font-black text-gray-900">
                                <span class="bg-gray-100 px-3 py-1 rounded-lg text-xs tracking-wider">{{ setting.key_name }}</span>
                            </td>
                            <td class="py-4 px-6 font-bold text-gray-600 break-all max-w-xs">{{ setting.value }}</td>
                            <td class="py-4 px-6 text-xs font-bold text-gray-400">{{ setting.description }}</td>
                            <td class="py-4 px-6 text-right">
                                <button @click="openEditSetting(setting)" class="text-blue-600 hover:text-blue-800 font-black text-sm mx-2 px-2 py-1 bg-blue-50 rounded-lg">Editar</button>
                                <button @click="deleteSetting(setting.id)" class="text-red-600 hover:text-red-800 font-black text-sm mx-2 px-2 py-1 bg-red-50 rounded-lg">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL PROMOS -->
        <div v-if="showPromoModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6 lg:p-8 animate-scale-in flex flex-col max-h-[90vh]">
                <h3 class="text-2xl font-black text-gray-900 mb-6">{{ isEditingPromo ? 'Editar Promoción' : 'Nueva Promoción' }}</h3>
                <div class="overflow-y-auto custom-scrollbar pr-2 flex-1 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nombre de Promoción</label>
                        <input v-model="promoForm.name" type="text" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-yellow-400 focus:bg-white outline-none transition font-bold" placeholder="Ej. Lunes Loco">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Tipo de Promoción</label>
                        <select v-model="promoForm.type" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-yellow-400 outline-none font-bold">
                            <option value="percentage">Porcentaje Descuento (%)</option>
                            <option value="fixed">Monto Fijo ($)</option>
                            <option value="free_product">Producto Gratis 🎁</option>
                        </select>
                    </div>
                    
                    <div v-if="promoForm.type === 'percentage' || promoForm.type === 'fixed'">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Valor ({{ promoForm.type === 'percentage' ? '%' : '$' }})</label>
                        <input v-model="promoForm.value" type="number" step="0.01" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-yellow-400 outline-none font-black text-lg">
                    </div>
                    
                    <div v-if="promoForm.type === 'free_product'">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Buscar Producto a Regalar</label>
                        <input type="text" list="products-list" v-model="promoForm.free_product_id" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-yellow-400 outline-none font-bold" placeholder="Escribe para buscar producto..." />
                        <datalist id="products-list">
                            <option v-for="prod in products" :key="prod.id" :value="prod.id">{{ prod.name }}</option>
                        </datalist>
                        <p class="text-[10px] text-gray-400 mt-1 pl-1">Ingresa o selecciona el ID del producto</p>
                    </div>

                    <label class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-100 transition mt-2">
                        <input type="checkbox" v-model="promoForm.is_active" class="w-5 h-5 rounded text-yellow-500 focus:ring-yellow-400 border-gray-300">
                        <span class="text-sm font-black text-gray-700">Promoción Activa</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                    <button @click="closePromoModal" class="px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition">Cancelar</button>
                    <button @click="savePromo" class="px-6 py-2.5 bg-yellow-400 text-gray-900 font-black rounded-xl shadow-md hover:bg-yellow-500 transition">Guardar</button>
                </div>
            </div>
        </div>

        <!-- MODAL DRIVERS -->
        <div v-if="showDriverModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6 lg:p-8 animate-scale-in">
                <h3 class="text-2xl font-black text-gray-900 mb-6">{{ isEditingDriver ? 'Editar Repartidor' : 'Nuevo Repartidor' }}</h3>
                <div class="space-y-4">
                    <input v-model="driverForm.name" type="text" placeholder="Nombre completo" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold outline-none focus:ring-2 focus:ring-yellow-400">
                    <input v-model="driverForm.phone" type="text" placeholder="Teléfono" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold outline-none focus:ring-2 focus:ring-yellow-400">
                    <input v-model="driverForm.vehicle_plate" type="text" placeholder="Placa del vehículo" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold outline-none focus:ring-2 focus:ring-yellow-400">
                    <label class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200 cursor-pointer mt-2">
                        <input type="checkbox" v-model="driverForm.is_active" class="w-5 h-5 rounded text-yellow-500">
                        <span class="text-sm font-black text-gray-700">Repartidor Activo</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeDriverModal" class="px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl">Cancelar</button>
                    <button @click="saveDriver" class="px-6 py-2.5 bg-yellow-400 text-gray-900 font-black rounded-xl">Guardar</button>
                </div>
            </div>
        </div>

        <!-- MODAL SETTINGS -->
        <div v-if="showSettingModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6 lg:p-8 animate-scale-in">
                <h3 class="text-2xl font-black text-gray-900 mb-6">{{ isEditingSetting ? 'Editar Parámetro' : 'Nuevo Parámetro' }}</h3>
                <div class="space-y-4">
                    <input v-model="settingForm.key_name" type="text" placeholder="Llave (ej: delivery_fee)" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold" :disabled="isEditingSetting">
                    <input v-model="settingForm.value" type="text" placeholder="Valor" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold">
                    <input v-model="settingForm.description" type="text" placeholder="Descripción breve" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 font-bold">
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeSettingModal" class="px-5 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-xl">Cancelar</button>
                    <button @click="saveSetting" class="px-6 py-2.5 bg-gray-900 text-white font-black rounded-xl">Guardar</button>
                </div>
            </div>
        </div>
        
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
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
.hide-scroll-if-possible {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.hide-scroll-if-possible::-webkit-scrollbar {
    display: none;
}
.animate-scale-in {
    animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes scaleIn {
    0% { opacity: 0; transform: scale(0.97) translateY(10px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
</style>
