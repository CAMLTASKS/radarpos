<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: Array,
});

const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'cashier',
});

const isEditing = ref(false);
const showModal = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (user) => {
    isEditing.value = true;
    form.clearErrors();
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const saveUser = () => {
    if (isEditing.value) {
        form.put(`/users/${form.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/users', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (id) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="Usuarios" />
    <AdminLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900">Usuarios del Sistema</h1>
                    <p class="text-gray-500 mt-1">Administra los cajeros y administradores del ERP.</p>
                </div>
                <button @click="openCreateModal" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-6 py-3 rounded-xl font-bold transition shadow-sm">
                    + Nuevo Usuario
                </button>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto pb-2 -mb-2">
                <table class="w-full text-left border-collapse min-w-[600px] whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="p-4 font-bold text-gray-600 text-sm uppercase tracking-wider">Nombre</th>
                            <th class="p-4 font-bold text-gray-600 text-sm uppercase tracking-wider">Email</th>
                            <th class="p-4 font-bold text-gray-600 text-sm uppercase tracking-wider">Rol</th>
                            <th class="p-4 font-bold text-gray-600 text-sm uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-gray-900">{{ user.name }}</td>
                            <td class="p-4 text-gray-600">{{ user.email }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold" :class="user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                                    {{ user.role === 'admin' ? 'Administrador' : 'Cajero' }}
                                </span>
                            </td>
                            <td class="p-4 flex gap-2 justify-end">
                                <button @click="openEditModal(user)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">✏️ Editar</button>
                                <button @click="deleteUser(user.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" :disabled="user.id === $page.props.auth?.user?.id">🗑️ Borrar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <div v-if="users.length === 0" class="p-8 text-center text-gray-500">
                    No hay usuarios registrados.
                </div>
            </div>
        </div>

        <!-- Modal Crear/Editar Usuario -->
        <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-end lg:items-center justify-center p-0 lg:p-4">
            <div class="bg-white rounded-t-3xl lg:rounded-3xl w-full max-w-md p-6 shadow-2xl animate-slide-up lg:animate-scale-in max-h-[85vh] lg:max-h-none overflow-y-auto">
                <h2 class="text-2xl font-black text-gray-900 mb-6">{{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
                
                <form @submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                        <input v-model="form.name" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico</label>
                        <input v-model="form.email" type="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contraseña <span v-if="isEditing" class="text-gray-400 font-normal">(Dejar en blanco para no cambiar)</span></label>
                        <input v-model="form.password" type="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none" :required="!isEditing">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Rol</label>
                        <select v-model="form.role" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-yellow-400 outline-none">
                            <option value="cashier">Cajero</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="closeModal" class="flex-1 bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 transition">Cancelar</button>
                        <button type="submit" class="flex-1 bg-yellow-400 text-gray-900 font-bold py-3 rounded-xl hover:bg-yellow-500 transition" :disabled="form.processing">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
@keyframes scale-in {
    0% { opacity: 0; transform: scale(0.98); }
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
