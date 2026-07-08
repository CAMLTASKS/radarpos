<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    products: Array,
    ingredients: Array,
    categories: Array
});

const showModal = ref(false);
const editingProductId = ref(null);
const form = ref({
    name: '',
    price: 0,
    category: 'Todo con helado',
    image: '🍨',
    is_addon: false,
    ingredients: [],
    variations: []
});

const icons = [
    '🍨', '🍦', '🥤', '☕', '🍹', '🍧', '🍰', '🧇', '🍾', '🍓', '🍋',
    '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>',
    '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
    '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
    '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>'
];

const newIngredient = ref('');
const newIngredientQty = ref(1);
const newVariationName = ref('');
const newVariationPrice = ref(0);

const openNewProductModal = () => {
    editingProductId.value = null;
    form.value = { name: '', price: 0, category: 'Todo con helado', image: '🍨', is_addon: false, ingredients: [], variations: [] };
    showModal.value = true;
};

const openEditProductModal = (product) => {
    editingProductId.value = product.id;
    form.value = {
        name: product.name,
        price: product.price,
        category: product.category,
        image: product.image,
        is_addon: product.is_addon,
        ingredients: product.ingredients.map(i => ({
            id: i.id,
            name: i.name,
            quantity: i.pivot.quantity,
            unit: i.unit
        })),
        variations: product.variations ? product.variations.map(v => ({ ...v })) : []
    };
    showModal.value = true;
};

const addIngredient = () => {
    const ing = props.ingredients.find(i => i.id == newIngredient.value);
    if (ing) {
        form.value.ingredients.push({ id: ing.id, name: ing.name, quantity: newIngredientQty.value, unit: ing.unit });
    }
};

const addVariation = () => {
    if (newVariationName.value) {
        form.value.variations.push({ name: newVariationName.value, price_modifier: newVariationPrice.value });
        newVariationName.value = '';
        newVariationPrice.value = 0;
    }
};

const saveProduct = async () => {
    try {
        if (editingProductId.value) {
            await axios.put(`/api/products/${editingProductId.value}`, form.value);
            window.toast("Producto actualizado correctamente");
        } else {
            await axios.post('/api/products', form.value);
            window.toast("Producto creado correctamente");
        }
        showModal.value = false;
        router.reload();
    } catch (e) {
        window.toast("Error al guardar", "error");
    }
};

const deleteProduct = async (id) => {
    if (confirm("¿Estás seguro de inhabilitar este producto?")) {
        try {
            await axios.delete(`/api/products/${id}`);
            window.toast("Producto inhabilitado");
            showModal.value = false;
            router.reload();
        } catch (e) {
            window.toast("Error al inhabilitar", "error");
        }
    }
};
</script>

<template>
    <Head title="Productos (CRUD)" />
    <AdminLayout>
        
        <div class="flex justify-between items-center mb-6 bg-white p-5 rounded-2xl shadow-sm border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Inventario y Productos</h1>
            <button @click="openNewProductModal" class="bg-yellow-400 hover:bg-yellow-500 font-black px-6 py-3 rounded-xl shadow-md transition text-sm">
                + Crear Producto
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 pb-20 lg:pb-0">
            <div v-for="product in products" :key="product.id" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-lg transition">
                <div class="p-6 text-center flex-1 relative">
                    <button @click="openEditProductModal(product)" class="absolute top-2 right-2 text-gray-400 hover:text-blue-500 p-2 transition">
                        ✏️
                    </button>
                    <button @click="deleteProduct(product.id)" class="absolute top-2 left-2 text-gray-400 hover:text-red-500 p-2 transition" title="Inhabilitar">
                        🗑️
                    </button>
                    <div class="text-6xl mb-4 flex justify-center items-center" v-html="product.image.includes('<svg') ? product.image : product.image"></div>
                    <h3 class="text-lg font-black text-gray-800 mb-1">{{ product.name }}</h3>
                    <p class="text-yellow-600 font-black text-xl mb-4">${{ parseFloat(product.price).toFixed(2) }}</p>
                    <span v-if="product.is_addon" class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded font-bold uppercase tracking-wider mb-3">Extra / Adicional</span>
                    
                    <div class="text-left bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Escandallo (Costo Real):</p>
                        <ul class="text-xs text-gray-700 font-bold space-y-1">
                            <li v-for="ing in product.ingredients" :key="ing.id" class="flex justify-between border-b border-gray-200 pb-1">
                                <span>{{ ing.name }}</span>
                                <span>{{ ing.pivot.quantity }} {{ ing.unit }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="product.variations && product.variations.length > 0" class="text-left mt-3">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Variaciones:</p>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="varItem in product.variations" :key="varItem.id" class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded font-bold border border-yellow-200">
                                {{ varItem.name }} (+${{ varItem.price_modifier }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CREACIÓN DE PRODUCTO -->
        <Teleport to="body">
        <div v-if="showModal" @click.self="showModal = false" @keydown.esc.window="showModal = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
            <div class="bg-white rounded-t-3xl lg:rounded-3xl w-full max-w-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh] animate-slide-up lg:animate-scale-in">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="text-xl font-black text-gray-800">{{ editingProductId ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-800 font-black">✕</button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="col-span-1 lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">Icono del Producto (Emoji o SVG)</label>
                            <input v-model="form.image" type="text" placeholder="Pega un emoji 🍨 o un código <svg>..." class="w-full bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 font-bold mb-2">
                            <div v-if="form.image" class="mt-2 p-4 bg-gray-100 rounded-xl inline-block text-4xl">
                                <span v-html="form.image.includes('<svg') ? form.image : form.image"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Nombre del Producto</label>
                            <input v-model="form.name" type="text" class="w-full bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Precio Base ($)</label>
                            <input v-model="form.price" type="number" class="w-full bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 font-bold">
                        </div>
                        <div class="col-span-1 lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Categoría</label>
                            <SearchableSelect 
                                v-model="form.category" 
                                :options="categories.map(c => ({ value: c, label: c }))"
                                placeholder="Seleccionar categoría..."
                            />
                        </div>
                        <div class="col-span-1 lg:col-span-2 flex items-center space-x-3 mt-2 p-3 bg-yellow-50 rounded-xl border border-yellow-100">
                            <input v-model="form.is_addon" type="checkbox" id="is_addon" class="w-5 h-5 text-yellow-500 bg-white border-gray-300 rounded focus:ring-yellow-400 focus:ring-2">
                            <label for="is_addon" class="text-sm font-bold text-gray-800">¿Es un producto adicional / extra?</label>
                        </div>
                    </div>

                    <!-- Insumos (Receta) -->
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase">Escandallo (Insumos de Inventario)</h3>
                        <div class="flex gap-2 mb-3">
                            <SearchableSelect 
                                class="flex-1"
                                v-model="newIngredient" 
                                :options="ingredients.map(ing => ({ value: ing.id, label: `${ing.name} (${ing.unit})` }))"
                                placeholder="Seleccionar insumo..."
                            />
                            <input v-model="newIngredientQty" type="number" placeholder="Cant." class="w-24 bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400">
                            <button @click="addIngredient" type="button" class="bg-gray-800 text-white font-bold px-4 rounded-xl">Añadir</button>
                        </div>
                        <ul class="bg-gray-50 p-3 rounded-xl space-y-1">
                            <li v-for="(ing, idx) in form.ingredients" :key="idx" class="flex justify-between items-center text-sm font-bold border-b border-gray-200 pb-1">
                                <span>{{ ing.name }}</span>
                                <div class="flex items-center gap-2">
                                    <span>{{ ing.quantity }} {{ ing.unit }}</span>
                                    <button @click="form.ingredients.splice(idx, 1)" type="button" class="text-red-500 hover:text-red-700 ml-2">✕</button>
                                </div>
                            </li>
                            <li v-if="form.ingredients.length === 0" class="text-xs text-gray-400">Sin insumos asignados</li>
                        </ul>
                    </div>

                    <!-- Variaciones -->
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase">Variaciones de Tamaño</h3>
                        <div class="flex gap-2 mb-3">
                            <input v-model="newVariationName" type="text" placeholder="Ej: Familiar" class="flex-1 bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 text-sm">
                            <input v-model="newVariationPrice" type="number" placeholder="Precio Extra ($)" class="w-32 bg-gray-50 border-0 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 text-sm">
                            <button @click="addVariation" type="button" class="bg-gray-800 text-white font-bold px-4 rounded-xl">Añadir</button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="(varItem, idx) in form.variations" :key="idx" class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-lg font-bold border border-yellow-300 flex items-center gap-2">
                                {{ varItem.name }} (+${{ varItem.price_modifier }})
                                <button @click="form.variations.splice(idx, 1)" type="button" class="text-red-500 hover:text-red-700">✕</button>
                            </span>
                        </div>
                    </div>

                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50">
                    <button @click="saveProduct" class="w-full py-4 bg-yellow-400 hover:bg-yellow-500 rounded-xl font-black text-gray-900 text-lg transition shadow-md">
                        Guardar Producto y Receta
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #D1D5DB; 
  border-radius: 20px;
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
