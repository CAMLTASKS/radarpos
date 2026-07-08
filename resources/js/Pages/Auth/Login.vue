<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso al Sistema" />

    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center font-sans relative overflow-hidden">
        
        <!-- Premium background elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-yellow-300 opacity-20 blur-[120px] animate-pulse"></div>
            <div class="absolute bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-pink-300 opacity-20 blur-[150px]"></div>
            <div class="absolute top-[40%] left-[20%] w-[30%] h-[30%] rounded-full bg-blue-300 opacity-20 blur-[100px]"></div>
            
            <div class="absolute inset-0 bg-white/40 backdrop-blur-[2px]"></div>
        </div>

        <div class="w-full max-w-md relative z-10 mx-4">
            
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 rounded-[2rem] shadow-2xl p-10 flex flex-col items-center">
                
                <div class="w-28 h-28 rounded-3xl bg-white shadow-xl flex items-center justify-center p-3 mb-6 transform -translate-y-16 absolute top-0 border border-gray-100">
                    <img src="/images/logo.png" alt="Sunber Logo" class="w-full h-full object-contain filter drop-shadow-md" />
                </div>

                <div class="mt-12 text-center mb-8">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Bienvenido</h2>
                    <p class="text-gray-500 font-medium mt-2">Ingresa a tu panel de administración</p>
                </div>

                <div v-if="status" class="mb-4 font-bold text-sm text-green-700 bg-green-50 p-3 rounded-xl border border-green-200 w-full text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="w-full space-y-5">
                    
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Correo Electrónico</label>
                        <input 
                            id="email" 
                            type="email" 
                            class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3.5 text-gray-900 font-medium focus:ring-2 focus:ring-yellow-400 focus:bg-white transition-all shadow-inner" 
                            v-model="form.email" 
                            required 
                            autofocus 
                            autocomplete="username" 
                            placeholder="usuario@ejemplo.com"
                        />
                        <div v-show="form.errors.email" class="text-xs text-red-500 font-bold mt-1.5">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Contraseña</label>
                        </div>
                        <input 
                            id="password" 
                            type="password" 
                            class="w-full bg-gray-50 border-0 rounded-xl px-4 py-3.5 text-gray-900 font-medium focus:ring-2 focus:ring-yellow-400 focus:bg-white transition-all shadow-inner" 
                            v-model="form.password" 
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                        />
                        <div v-show="form.errors.password" class="text-xs text-red-500 font-bold mt-1.5">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="remember" v-model="form.remember" class="w-5 h-5 border-2 border-gray-300 rounded text-yellow-400 focus:ring-yellow-400 focus:ring-offset-0 peer appearance-none checked:bg-yellow-400 checked:border-yellow-400 transition-colors" />
                                <svg class="absolute w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="ms-2 text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Recordarme</span>
                        </label>
                    </div>

                    <div class="mt-8 pt-2">
                        <button 
                            class="w-full py-4 bg-gray-900 hover:bg-black rounded-xl font-black text-white text-lg transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex justify-center items-center gap-2" 
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }" 
                            :disabled="form.processing"
                        >
                            <span>Ingresar al Sistema</span>
                            <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            <svg v-else class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-sm font-medium text-gray-500">
                    &copy; {{ new Date().getFullYear() }} Sunber. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</template>
