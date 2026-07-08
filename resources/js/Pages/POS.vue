<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import axios from 'axios';

const formatPrice = (value) => {
    return Number(value).toLocaleString('es-CO');
};

const props = defineProps({
    products: Array,
    activePromotions: Array,
    defaultDeliveryFee: Number,
});

// -- ESTADO DE CAJA REGISTRADORA --
const isRegisterOpen = ref(true);
const openingAmount = ref('');

const checkRegisterStatus = async () => {
    try {
        const res = await axios.get('/api/cash-registers/status');
        isRegisterOpen.value = res.data.is_open;
    } catch(e) {
        console.error('Error fetching register status', e);
        isRegisterOpen.value = false;
    }
};

const openRegister = async () => {
    if(!openingAmount.value || openingAmount.value < 0) return alert('Ingresa una base válida');
    try {
        await axios.post('/api/cash-registers/open', { opening_amount: openingAmount.value });
        isRegisterOpen.value = true;
        alert('Caja Abierta Exitosamente');
    } catch(e) {
        alert(e.response?.data?.error || 'Error al abrir caja');
    }
};

const drivers = ref([]);
const fetchDrivers = async () => {
    try {
        const res = await axios.get('/api/drivers');
        drivers.value = res.data;
    } catch(e) {
        console.error(e);
    }
};

onMounted(() => {
    checkRegisterStatus();
    fetchDrivers();
});

// -- ESTADO DEL CARRITO Y CLIENTE --
const cart = ref([]);
const customerName = ref('');
const customerPhone = ref('');
const customerPoints = ref(0);
const customerOrdersCount = ref(0);
const countryCode = ref('57');

const paymentMethod = ref('Efectivo');
const deliveryType = ref('local');
const deliveryAddress = ref('');
const deliveryDriver = ref('');

const searchQuery = ref('');
const selectedCategory = ref('Todos');

const categoryOptions = computed(() => {
    const cats = new Set(props.products.map(p => p.category || 'General'));
    return [
        { value: 'Todos', label: 'Todas las Categorías' },
        ...Array.from(cats).map(c => ({ value: c, label: c }))
    ];
});

// Modal state
const selectedProduct = ref(null);
const showModal = ref(false);
const showLoyaltyModal = ref(false);
const showPromosModal = ref(false); // Variable agregada
const itemNotes = ref('');
const selectedVariation = ref(null);
const selectedAddons = ref([]);
const newAddonId = ref('');
const appliedPromoId = ref(null);
const availablePromotions = computed(() => props.activePromotions || []);

const addonOptions = computed(() => {
    const page = usePage();
    return (page.props.addons || []).map(a => ({
        value: a.id,
        label: `${a.name} (+ $${formatPrice(a.price)})`
    }));
});

const filteredProducts = computed(() => {
    let prods = props.products;
    if (selectedCategory.value !== 'Todos') {
        prods = prods.filter(p => (p.category || 'General') === selectedCategory.value);
    }
    if (searchQuery.value) {
        prods = prods.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
    }
    return prods;
});

// Búsqueda de cliente por teléfono
let searchTimeout;
watch(customerPhone, (newVal) => {
    if (newVal.length >= 7) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const phone = `${countryCode.value}${newVal.replace(/\D/g, '')}`;
                const response = await axios.get(`/api/customers/${phone}`);
                if (response.data) {
                    customerName.value = response.data.name;
                    customerPoints.value = response.data.points;
                    customerOrdersCount.value = response.data.orders_count;
                    
                    if (customerPoints.value > 0 || customerOrdersCount.value > 0) {
                        fetchPromotions();
                        showLoyaltyModal.value = true;
                    }
                }
            } catch (e) {
                // Not found
            }
        }, 500);
    } else {
        customerPoints.value = 0;
        customerOrdersCount.value = 0;
        appliedPromoId.value = null;
    }
});

const fetchPromotions = async () => {
    try {
        const res = await axios.get('/api/promotions?active=true');
        availablePromotions.value = res.data.filter(p => p.min_points <= customerPoints.value || p.min_orders <= customerOrdersCount.value);
    } catch(e) {}
};

const openProductModal = (product) => {
    selectedProduct.value = product;
    selectedVariation.value = product.variations && product.variations.length > 0 ? product.variations[0] : null;
    selectedAddons.value = [];
    itemNotes.value = '';
    showModal.value = true;
};

const closeProductModal = () => {
    showModal.value = false;
    selectedProduct.value = null;
    newAddonId.value = '';
};

watch(newAddonId, (newId) => {
    if (newId) {
        const page = usePage();
        const addon = page.props.addons.find(a => a.id === newId);
        if (addon) {
            incrementAddon(addon);
        }
        newAddonId.value = '';
    }
});

const incrementAddon = (addon) => {
    const index = selectedAddons.value.findIndex(a => a.id === addon.id);
    if(index === -1) {
        selectedAddons.value.push({...addon, qty: 1});
    } else {
        selectedAddons.value[index].qty++;
    }
};

const decrementAddon = (addon) => {
    const index = selectedAddons.value.findIndex(a => a.id === addon.id);
    if(index !== -1) {
        if(selectedAddons.value[index].qty > 1) {
            selectedAddons.value[index].qty--;
        } else {
            selectedAddons.value.splice(index, 1);
        }
    }
};

const addToCart = () => {
    let basePrice = parseFloat(selectedProduct.value.price);
    if (selectedVariation.value) {
        basePrice += parseFloat(selectedVariation.value.price_modifier);
    }
    
    let addonsTotal = 0;
    let addonsList = [];
    selectedAddons.value.forEach(a => {
        addonsTotal += (parseFloat(a.price) * a.qty);
        for(let i = 0; i < a.qty; i++){
            addonsList.push({ id: a.id, name: a.name, price: a.price });
        }
    });
    
    const cartItem = {
        id: Date.now(),
        product: selectedProduct.value,
        variation: selectedVariation.value,
        addons: addonsList,
        notes: itemNotes.value,
        unitPrice: basePrice + addonsTotal,
        quantity: 1
    };
    
    cart.value.push(cartItem);
    closeProductModal();
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const subtotal = computed(() => {
    return cart.value.reduce((acc, item) => acc + (item.unitPrice * item.quantity), 0);
});

const deliveryFee = ref(0);

watch(deliveryType, (newVal) => {
    if (newVal === 'local') {
        deliveryFee.value = 0;
    } else {
        deliveryFee.value = 0; // El usuario lo ingresará manualmente
    }
});

const total = computed(() => {
    let t = subtotal.value + (Number(deliveryFee.value) || 0);
    if (appliedPromoId.value) {
        const promo = availablePromotions.value.find(p => p.id === appliedPromoId.value);
        if (promo) {
            if (promo.type === 'percentage' || promo.discount_type === 'percent') {
                t -= (t * (promo.value || promo.discount_value) / 100);
            } else if (promo.type === 'fixed' || promo.discount_type === 'fixed') {
                t -= (promo.value || promo.discount_value);
            }
        }
    }
    return Math.max(0, t);
});

watch(appliedPromoId, (newId) => {
    if (newId) {
        const promo = availablePromotions.value.find(p => p.id === newId);
        if (promo && promo.type === 'free_product' && promo.free_product_id) {
            const freeProd = props.products.find(p => p.id == promo.free_product_id);
            if (freeProd) {
                cart.value.push({
                    id: Date.now() + Math.random(),
                    product: freeProd,
                    variation: null,
                    addons: [],
                    notes: `🎁 Promo: ${promo.name}`,
                    unitPrice: 0,
                    quantity: 1,
                    isFree: true
                });
                window.toast(`Producto gratis añadido: ${freeProd.name}`);
            }
        }
    } else {
        // Remove free items if promo removed
        cart.value = cart.value.filter(i => !i.isFree);
    }
});

const checkout = async () => {
    if (cart.value.length === 0) return window.toast('El carrito está vacío', 'error');
    if (!customerName.value && deliveryType.value === 'delivery') return window.toast('Por favor ingresa el nombre del cliente para el domicilio', 'error');
    if (deliveryType.value === 'delivery' && !deliveryAddress.value) return window.toast('Ingresa la dirección de entrega', 'error');
    
    try {
        const payload = {
            customer_name: customerName.value,
            customer_phone: customerPhone.value ? `${countryCode.value}${customerPhone.value.replace(/\D/g, '')}` : null,
            delivery_type: deliveryType.value,
            delivery_address: deliveryAddress.value,
            delivery_driver: deliveryDriver.value,
            delivery_fee: Number(deliveryFee.value) || 0,
            items: cart.value.map(i => ({
                product_id: i.product.id,
                variation_id: i.variation ? i.variation.id : null,
                notes: i.notes,
                quantity: i.quantity,
                addons: i.addons
            })),
            payment_method: paymentMethod.value,
            promotion_id: appliedPromoId.value,
            total: total.value
        };
        
        const response = await axios.post('/api/orders', payload);
        if (response.data.success) {
            window.toast(`Pedido #${response.data.order.id} procesado con éxito!`);
            window.open('/orders/' + response.data.order.id + '/receipt', '_blank');
            cart.value = [];
            customerName.value = '';
            customerPhone.value = '';
            deliveryAddress.value = '';
            deliveryDriver.value = '';
            paymentMethod.value = 'Efectivo';
            deliveryType.value = 'local';
            customerPoints.value = 0;
            customerOrdersCount.value = 0;
            appliedPromoId.value = null;
        }
    } catch (error) {
        console.error(error);
        alert(error.response?.data?.error || 'Error al crear el pedido');
    }
};
</script>

<template>
    <Head title="POS - Sunber" />
    <AdminLayout>
        
        <!-- PANTALLA BLOQUEO CAJA CERRADA -->
        <Teleport to="body">
        <div v-if="!isRegisterOpen" class="fixed inset-0 z-[60] bg-gray-900/90 backdrop-blur-md flex flex-col items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-10 max-w-md w-full shadow-2xl text-center">
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">🔐</span>
                </div>
                <h2 class="text-3xl font-black text-gray-800 mb-2">Caja Cerrada</h2>
                <p class="text-gray-500 mb-8 font-medium">Para iniciar a facturar, debes ingresar la base de efectivo inicial de tu turno.</p>
                
                <div class="text-left mb-6">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Base en Efectivo ($)</label>
                    <input v-model="openingAmount" type="number" class="w-full text-center text-3xl font-black bg-gray-50 border border-gray-200 rounded-2xl p-4 focus:ring-4 focus:ring-yellow-400/50 outline-none transition" placeholder="0">
                </div>
                
                <button @click="openRegister" class="w-full py-4 bg-yellow-400 hover:bg-yellow-500 rounded-xl font-black text-gray-900 text-lg transition shadow-lg transform hover:-translate-y-1">
                    Abrir Caja y Comenzar
                </button>
            </div>
        </div>
        </Teleport>

        <!-- PANTALLA POS -->
        <div v-if="isRegisterOpen" class="flex flex-col lg:flex-row gap-4 h-auto lg:h-[calc(100vh-100px)] pb-24 lg:pb-0">
            
            <!-- Left Side: Productos -->
            <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden min-h-[60vh] lg:min-h-0">
                <div class="p-3 border-b border-gray-100 flex items-center gap-3 bg-gray-50 shrink-0">
                    <select 
                        class="w-48 bg-white border border-gray-200 rounded-xl p-2 text-sm font-bold focus:ring-2 focus:ring-yellow-400 outline-none shadow-sm cursor-pointer"
                        v-model="selectedCategory" 
                    >
                        <option v-for="cat in categoryOptions" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                    </select>
                    
                    <div class="flex-1 relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input v-model="searchQuery" type="text" placeholder="Buscar producto..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 text-sm transition shadow-sm outline-none">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto pr-2 p-3 grid grid-cols-2 xl:grid-cols-4 gap-3 custom-scrollbar content-start">
                    <button v-for="product in filteredProducts" :key="product.id" @click="openProductModal(product)" class="bg-white border border-gray-100 rounded-2xl p-4 flex flex-col items-center text-center hover:border-yellow-400 hover:shadow-lg transition group">
                        <div class="text-6xl bg-gray-50 w-full rounded-2xl py-4 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform">
                            <span v-html="product.image || '🍨'"></span>
                        </div>
                        <h3 class="font-black text-gray-800 text-base leading-tight line-clamp-2 w-full">{{ product.name }}</h3>
                        <p class="text-yellow-600 font-black text-base mt-1 w-full">${{ formatPrice(product.price) }}</p>
                    </button>
                </div>
            </div>

            <!-- Right Side: Checkout y Cliente -->
            <div id="checkout-section" class="w-full lg:w-[450px] flex flex-col gap-4">
                
                <!-- Formulario Dinámico -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    <div class="flex bg-gray-100 p-1 m-2 rounded-xl">
                        <button @click="deliveryType = 'local'" :class="deliveryType === 'local' ? 'bg-white shadow-sm font-bold text-gray-900' : 'text-gray-500 font-medium hover:bg-gray-200'" class="flex-1 py-2 rounded-lg transition text-sm">Mesa / Local 🍽️</button>
                        <button @click="deliveryType = 'delivery'" :class="deliveryType === 'delivery' ? 'bg-yellow-400 shadow-sm font-bold text-gray-900' : 'text-gray-500 font-medium hover:bg-gray-200'" class="flex-1 py-2 rounded-lg transition text-sm">Domicilio 🚚</button>
                    </div>

                    <div class="p-4 pt-2 flex flex-col gap-3">
                        <div class="flex gap-2">
                            <div class="flex w-2/5 bg-gray-50 rounded-xl border border-gray-100 overflow-hidden focus-within:ring-2 focus-within:ring-yellow-400">
                                <span class="bg-gray-100 px-2 flex items-center text-xs text-gray-500 font-bold border-r border-gray-200">+57</span>
                                <input v-model="customerPhone" type="text" placeholder="Teléfono" class="w-full bg-transparent border-0 p-2 text-sm font-bold outline-none">
                            </div>
                            <input v-model="customerName" type="text" placeholder="Nombre (Opcional en local)" class="w-3/5 bg-gray-50 border border-gray-100 rounded-xl p-2 text-sm font-medium focus:ring-2 focus:ring-yellow-400 outline-none">
                        </div>

                        <!-- Detalles extra para Domicilio -->
                        <div v-if="deliveryType === 'delivery'" class="flex flex-col gap-2 animate-fade-in-down">
                            <input v-model="deliveryAddress" type="text" placeholder="📍 Dirección Completa" class="w-full bg-orange-50 border border-orange-100 rounded-xl p-2 text-sm font-bold text-orange-900 placeholder-orange-400 focus:ring-2 focus:ring-orange-400 outline-none">
                            <select v-model="deliveryDriver" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm font-medium focus:ring-2 focus:ring-yellow-400 outline-none shadow-sm transition">
                                <option value="">🚚 Asignar Repartidor (Opcional)</option>
                                <option v-for="d in drivers" :key="d.id" :value="d.name">{{ d.name }}</option>
                            </select>
                            <div class="flex gap-2 items-center bg-gray-50 p-2 rounded-xl border border-gray-100 mt-1">
                                <label class="text-sm font-bold text-gray-700 whitespace-nowrap">Tarifa Domicilio $</label>
                                <input v-model="deliveryFee" type="number" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm font-bold focus:ring-2 focus:ring-yellow-400 outline-none text-right">
                            </div>
                        </div>
                        
                        <!-- Etiqueta Fidelización y Botón de Promos -->
                        <div v-if="customerOrdersCount > 0" class="flex justify-between items-center bg-gradient-to-r from-green-50 to-emerald-50 border border-green-100 p-2 rounded-xl mt-1">
                            <div class="text-green-700 text-xs font-bold flex items-center">
                                ⭐ Frecuente ({{ customerOrdersCount }} ped.)
                            </div>
                            <button @click="showPromosModal = true" class="text-xs bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-1 rounded-lg shadow-sm transition-colors">
                                Ver Promos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Orden -->
                <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
                    <div class="bg-gray-50 p-3 border-b border-gray-100">
                        <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wider flex justify-between">
                            <span>Resumen de Orden</span>
                            <span class="text-gray-400">{{ cart.length }} items</span>
                        </h2>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                        <div v-for="(item, index) in cart" :key="item.id" class="bg-white border border-gray-100 p-3 rounded-xl shadow-sm relative group">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-bold text-gray-900">{{ item.quantity }}x {{ item.product.name }}</span>
                                    <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                                        <p v-if="item.variation">• {{ item.variation.name }}</p>
                                        <p v-for="addon in item.addons" :key="addon.id">• Extra: {{ addon.name }} (+${{ formatPrice(addon.price) }})</p>
                                        <p v-if="item.notes" class="text-yellow-600 font-bold">Nota: {{ item.notes }}</p>
                                    </div>
                                </div>
                                <span class="font-black text-gray-900">${{ formatPrice(item.unitPrice * item.quantity) }}</span>
                            </div>
                            <button @click="removeFromCart(index)" class="absolute top-2 right-2 bg-red-100 text-red-600 w-6 h-6 rounded-md flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-red-500 hover:text-white">✕</button>
                        </div>
                        
                        <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                            <span class="text-4xl mb-2 opacity-50">🛒</span>
                            <p class="font-bold text-sm">Carrito vacío</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between text-gray-500 text-sm font-medium">
                            <span>Subtotal</span>
                            <span>${{ formatPrice(subtotal) }}</span>
                        </div>
                        <div v-if="deliveryType === 'delivery'" class="flex justify-between text-sm font-bold" :class="deliveryFee === 0 ? 'text-green-600' : 'text-gray-500'">
                            <span>Domicilio <span v-if="deliveryFee === 0" class="bg-green-100 px-1 rounded ml-1 text-xs">Promo</span></span>
                            <span>{{ deliveryFee === 0 ? 'GRATIS' : '$' + formatPrice(deliveryFee) }}</span>
                        </div>
                        <div v-if="appliedPromoId" class="flex justify-between items-center text-sm font-bold text-green-600 bg-green-50 p-2 rounded-lg mt-2">
                            <span>Promo Aplicada ⭐ ({{ availablePromotions.find(p => p.id === appliedPromoId)?.name }})</span>
                            <div class="flex items-center gap-2">
                                <span v-if="availablePromotions.find(p => p.id === appliedPromoId)?.type !== 'free_product'">-</span>
                                <button @click="appliedPromoId = null" class="text-red-500 hover:text-red-700 bg-red-100 rounded-full w-5 h-5 flex items-center justify-center">✕</button>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-gray-900 pt-2 border-t border-gray-200 mt-2">
                            <span class="font-black text-lg">TOTAL</span>
                            <span class="text-yellow-600 font-black text-2xl">${{ formatPrice(total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pago y Botón Final -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <button @click="paymentMethod = 'Efectivo'" class="flex flex-col items-center justify-center rounded-xl py-3 px-1 font-bold text-xs transition border-2 hover:shadow-md" :class="paymentMethod === 'Efectivo' ? 'bg-green-50 border-green-500 text-green-700 shadow-inner' : 'bg-white border-gray-200 text-gray-600 hover:border-green-300'">
                            <span class="text-xl mb-1">💵</span>
                            Efectivo
                        </button>
                        <button @click="paymentMethod = 'Nequi'" class="flex flex-col items-center justify-center rounded-xl py-3 px-1 font-bold text-xs transition border-2 hover:shadow-md" :class="paymentMethod === 'Nequi' ? 'bg-purple-50 border-purple-500 text-purple-700 shadow-inner' : 'bg-white border-gray-200 text-gray-600 hover:border-purple-300'">
                            <span class="text-xl mb-1">🟣</span>
                            Nequi
                        </button>
                        <button @click="paymentMethod = 'Daviplata'" class="flex flex-col items-center justify-center rounded-xl py-3 px-1 font-bold text-xs transition border-2 hover:shadow-md" :class="paymentMethod === 'Daviplata' ? 'bg-red-50 border-red-500 text-red-700 shadow-inner' : 'bg-white border-gray-200 text-gray-600 hover:border-red-300'">
                            <span class="text-xl mb-1">🔴</span>
                            Daviplata
                        </button>
                    </div>

                    <button @click="checkout" class="w-full py-4 bg-gray-900 hover:bg-black rounded-xl font-black text-white text-lg transition shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        Cobrar Orden <span>${{ formatPrice(total) }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL DE PROMOS FRECUENTES -->
        <Teleport to="body">
        <div v-if="showPromosModal" @click.self="showPromosModal = false" @keydown.esc.window="showPromosModal = false" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
            <div class="bg-white rounded-t-3xl lg:rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl flex flex-col animate-slide-up lg:animate-scale-in">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-50 to-emerald-50">
                    <h2 class="text-lg font-black text-green-800 flex items-center gap-2">⭐ Promociones Activas</h2>
                    <button @click="showPromosModal = false" class="text-gray-400 hover:text-gray-800">✕</button>
                </div>
                <div class="p-4 flex-1 overflow-y-auto space-y-3 max-h-[60vh] custom-scrollbar">
                    <div v-if="availablePromotions.length === 0" class="text-center text-gray-500 text-sm py-4 font-bold">
                        No hay promociones activas en este momento.
                    </div>
                    <button 
                        v-for="promo in availablePromotions" :key="promo.id"
                        @click="appliedPromoId = promo.id; showPromosModal = false"
                        class="w-full text-left p-4 rounded-xl border transition-all"
                        :class="appliedPromoId === promo.id ? 'border-green-500 bg-green-50 shadow-md ring-2 ring-green-200' : 'border-gray-200 hover:border-yellow-400 hover:shadow-sm'"
                    >
                        <h3 class="font-black text-gray-800">{{ promo.name }}</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">
                            <span v-if="promo.type === 'percentage' || promo.discount_type === 'percent'">Descuento del {{ promo.value || promo.discount_value }}% en toda la orden.</span>
                            <span v-else-if="promo.type === 'fixed' || promo.discount_type === 'fixed'">Descuento directo de ${{ formatPrice(promo.value || promo.discount_value) }}.</span>
                            <span v-else-if="promo.type === 'free_product'">Producto Gratis 🎁 al facturar.</span>
                        </p>
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- MODAL DE PERSONALIZACIÓN ÁGIL -->
        <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
            <div class="bg-white rounded-t-3xl lg:rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[85vh] lg:max-h-[90vh] animate-slide-up lg:animate-scale-in">
                
                <!-- Cabecera modal -->
                <div class="p-5 border-b border-gray-100 flex items-center gap-4 bg-gray-50">
                    <div class="text-4xl bg-white w-16 h-16 rounded-xl flex items-center justify-center shadow-sm">
                        {{ selectedProduct.image || '🍨' }}
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-black text-gray-900 leading-tight">{{ selectedProduct.name }}</h2>
                        <p class="text-yellow-600 font-bold">${{ formatPrice(selectedProduct.price) }} Base</p>
                    </div>
                    <button @click="closeProductModal" class="bg-gray-200 text-gray-500 hover:bg-gray-300 w-8 h-8 rounded-full font-bold">✕</button>
                </div>
                
                <div class="p-5 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
                    
                    <!-- Variaciones rápidas -->
                    <div v-if="selectedProduct.variations && selectedProduct.variations.length > 0">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Tamaño / Variación</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="variation in selectedProduct.variations" :key="variation.id" @click="selectedVariation = variation" 
                                class="border-2 rounded-xl p-3 text-left transition relative" 
                                :class="selectedVariation && selectedVariation.id === variation.id ? 'border-yellow-400 bg-yellow-50' : 'border-gray-100 hover:border-gray-200'">
                                <div v-if="selectedVariation && selectedVariation.id === variation.id" class="absolute top-2 right-2 text-yellow-600 text-sm">✓</div>
                                <p class="font-bold text-gray-900 text-sm">{{ variation.name }}</p>
                                <p class="text-xs font-bold text-gray-500 mt-1">+${{ formatPrice(variation.price_modifier) }}</p>
                            </button>
                        </div>
                    </div>

                    <!-- Adicionales con buscador -->
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Adicionales</h3>
                        <div class="mb-3">
                            <SearchableSelect
                                v-model="newAddonId"
                                :options="addonOptions"
                                placeholder="Buscar adicional..."
                            />
                        </div>
                        <div class="space-y-2">
                            <div v-for="addon in selectedAddons" :key="addon.id" class="flex items-center justify-between border border-gray-100 rounded-xl p-2 pl-4 hover:bg-gray-50 transition">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ addon.name }}</p>
                                    <p class="text-xs font-bold text-green-600">+${{ formatPrice(addon.price) }}</p>
                                </div>
                                <div class="flex items-center gap-3 bg-gray-100 rounded-lg p-1">
                                    <button @click="decrementAddon(addon)" class="w-8 h-8 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 font-bold hover:bg-red-50 transition">-</button>
                                    <span class="font-black text-gray-900 w-4 text-center">{{ addon.qty }}</span>
                                    <button @click="incrementAddon(addon)" class="w-8 h-8 flex items-center justify-center bg-yellow-400 rounded shadow-sm text-gray-900 font-bold hover:bg-yellow-500 transition">+</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Notas Especiales</h3>
                        <input v-model="itemNotes" type="text" placeholder="Ej: Sin queso, doble servilleta..." class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none text-sm font-medium">
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 bg-white">
                    <button @click="addToCart" class="w-full py-4 bg-yellow-400 text-gray-900 hover:bg-yellow-500 rounded-xl font-black text-lg transition shadow-md flex justify-center gap-2">
                        <span>Añadir al Carrito</span>
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Modal de Fidelidad -->
        <Teleport to="body">
        <div v-if="showLoyaltyModal" class="fixed inset-0 z-50 flex items-end lg:items-center justify-center bg-gray-900/50 backdrop-blur-sm p-0 lg:p-4 animate-fade-in-down">
            <div class="bg-white rounded-t-3xl lg:rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh] animate-slide-up lg:animate-scale-in">
                
                <div class="p-6 border-b border-gray-100 text-center bg-yellow-50">
                    <div class="text-5xl mb-4">🏆</div>
                    <h2 class="text-2xl font-black text-gray-900 leading-tight">¡Cliente Frecuente!</h2>
                    <p class="text-yellow-700 font-bold mt-1">{{ customerName }}</p>
                </div>
                
                <div class="p-6 text-center space-y-4">
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Puntos Acumulados</p>
                        <p class="text-4xl font-black text-yellow-500">{{ customerPoints }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Compras</p>
                            <p class="text-2xl font-black text-gray-900">{{ customerOrdersCount }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Nivel</p>
                            <p class="text-xl font-black text-blue-500">
                                {{ customerPoints > 500 ? 'Platino' : (customerPoints > 200 ? 'Oro' : 'Plata') }}
                            </p>
                        </div>
                    </div>
                    
                    <div v-if="availablePromotions.length > 0" class="mt-4">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">Promociones Disponibles</p>
                        <select v-model="appliedPromoId" class="w-full bg-white border border-yellow-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 font-bold text-yellow-700">
                            <option :value="null">No aplicar promoción</option>
                            <option v-for="promo in availablePromotions" :key="promo.id" :value="promo.id">
                                {{ promo.name }} (-{{ promo.discount_type === 'percent' ? promo.discount_value + '%' : '$' + formatPrice(promo.discount_value) }})
                            </option>
                        </select>
                    </div>
                    
                    <p class="text-sm text-gray-600 font-medium px-4">
                        Recuerda ofrecerle redimir sus puntos o brindarle una promoción especial por su lealtad.
                    </p>
                </div>

                <div class="p-5 border-t border-gray-100 bg-white">
                    <button @click="showLoyaltyModal = false" class="w-full py-4 bg-gray-900 text-white hover:bg-black rounded-xl font-black text-lg transition shadow-md">
                        Entendido, continuar con la venta
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #E5E7EB; 
  border-radius: 20px;
}
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