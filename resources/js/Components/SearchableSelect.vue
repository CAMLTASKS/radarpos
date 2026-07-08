<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    options: {
        type: Array,
        required: true // [{ value: 1, label: 'Option 1' }]
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...'
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const selectRef = ref(null);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    return props.options.filter(opt => 
        opt.label.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const selectOption = (option) => {
    emit('update:modelValue', option.value);
    searchQuery.value = option.label;
    isOpen.value = false;
};

const handleFocus = (e) => {
    isOpen.value = true;
    e.target.select();
};

watch(() => props.modelValue, (newVal) => {
    const opt = props.options.find(opt => opt.value === newVal);
    if (opt) {
        searchQuery.value = opt.label;
    } else {
        searchQuery.value = '';
    }
}, { immediate: true });

const closeOnOutsideClick = (e) => {
    if (selectRef.value && !selectRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeOnOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener('click', closeOnOutsideClick);
});
</script>

<template>
    <div class="relative" ref="selectRef">
        <input 
            type="text" 
            v-model="searchQuery" 
            @focus="handleFocus"
            @click="handleFocus"
            :placeholder="placeholder"
            class="w-full bg-white border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 font-medium shadow-sm transition outline-none"
        >
        
        <div v-if="isOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
            <ul class="max-h-60 overflow-y-auto custom-scrollbar">
                <li 
                    v-for="option in filteredOptions" 
                    :key="option.value"
                    @click="selectOption(option)"
                    class="p-3 text-sm hover:bg-yellow-50 cursor-pointer border-b border-gray-50 last:border-0 transition text-gray-700 font-medium"
                    :class="{'bg-yellow-100 text-yellow-800 font-bold': option.value === modelValue}"
                >
                    {{ option.label }}
                </li>
                <li v-if="filteredOptions.length === 0" class="p-4 text-center text-gray-400 text-sm">
                    No hay resultados
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #D1D5DB; 
  border-radius: 20px;
}
</style>
