<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ModuleCard from './Partials/ModuleCard.vue';

defineProps({
    modules: Array,
});

// Modal & Form State
const showFormModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    key: '',
    name: '',
    description: '',
    url: '',
    color: 'emerald',
    icon: '',
    is_active: true,
});

const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    type: 'danger',
    loading: false,
    action: null
});

const openConfirmFn = (options) => {
    confirmModal.value = {
        show: true,
        title: options.title || 'Konfirmasi',
        message: options.message,
        confirmText: options.confirmText || 'Ya, Lanjutkan',
        type: options.type || 'danger',
        loading: false,
        action: options.action
    };
};

const handleConfirm = () => {
    if (confirmModal.value.action) {
        confirmModal.value.loading = true;
        confirmModal.value.action({
            onFinish: () => {
                confirmModal.value.loading = false;
                confirmModal.value.show = false;
            }
        });
    } else {
        confirmModal.value.show = false;
    }
};

// Form Functions
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.color = 'emerald';
    form.is_active = true;
    showFormModal.value = true;
};

const openEditModal = (mod) => {
    isEditing.value = true;
    form.clearErrors();
    form.id = mod.id;
    form.key = mod.key;
    form.name = mod.name;
    form.description = mod.description;
    form.url = mod.url;
    form.color = mod.color || 'emerald';
    form.icon = mod.icon;
    form.is_active = !!mod.is_active;
    showFormModal.value = true;
};

const submitForm = () => {
    const options = {
        onSuccess: () => {
            showFormModal.value = false;
            form.reset();
        }
    };
    
    if (isEditing.value) {
        form.put(`/admin/modules/${form.id}`, options);
    } else {
        form.post('/admin/modules', options);
    }
};

const deleteModule = (mod) => {
    openConfirmFn({
        title: 'Hapus Modul',
        message: `Apakah Anda yakin ingin menghapus modul "${mod.name}"? Data terkait user access juga akan dihapus.`,
        confirmText: 'Hapus Modul',
        type: 'danger',
        action: (options) => router.delete(`/admin/modules/${mod.id}`, options)
    });
};

const toggleModule = (mod) => {
    openConfirmFn({
        title: mod.is_active ? 'Nonaktifkan Modul' : 'Aktifkan Modul',
        message: mod.is_active 
            ? `User tidak akan bisa mengakses modul "${mod.name}" lagi.` 
            : `User dengan akses modul ini akan bisa menggunakannya kembali.`,
        confirmText: mod.is_active ? 'Nonaktifkan' : 'Aktifkan',
        type: mod.is_active ? 'warning' : 'success',
        action: (options) => router.post(`/admin/modules/${mod.id}/toggle`, {}, options)
    });
};

const COLORS = {
    emerald: 'bg-emerald-500',
    blue: 'bg-blue-500',
    violet: 'bg-violet-500',
    amber: 'bg-amber-500',
    red: 'bg-red-500',
    indigo: 'bg-indigo-500',
    pink: 'bg-pink-500',
    cyan: 'bg-cyan-500',
    gray: 'bg-gray-500',
};

const getColorClass = (color) => COLORS[color] || COLORS['gray'];

const colorOptions = ['emerald', 'blue', 'violet', 'amber', 'red', 'indigo', 'pink', 'cyan', 'gray'];
</script>

<template>
    <AdminLayout title="Modul Management">
        <div class="space-y-6">
            <!-- Header -->
            <SectionHeader 
                title="Modul Management" 
                subtitle="Kelola modul aplikasi yang tersedia di portal"
            >
                <template #actions>
                    <button
                        @click="router.post('/admin/modules/sync')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync
                    </button>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Modul
                    </button>
                </template>
            </SectionHeader>

            <!-- Modules Grid -->
            <div v-if="modules.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <ModuleCard
                    v-for="mod in modules"
                    :key="mod.id"
                    :mod="mod"
                    @toggle="toggleModule"
                    @edit="openEditModal"
                    @delete="deleteModule"
                />
            </div>

            <!-- Empty State -->
            <EmptyState 
                v-else
                message="Belum ada modul"
                action="Sync dari Config"
                @action="router.post('/admin/modules/sync')"
            >
                <template #icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </template>
            </EmptyState>
        </div>

        <!-- Create/Edit Modal -->
        <TransitionRoot appear :show="showFormModal" as="template">
            <Dialog as="div" @close="showFormModal = false" class="relative z-50">
                <TransitionChild
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            enter="ease-out duration-300"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 shadow-xl border border-gray-200/80 dark:border-white/5">
                                <DialogTitle class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    {{ isEditing ? 'Edit Modul' : 'Tambah Modul Baru' }}
                                </DialogTitle>

                                <form @submit.prevent="submitForm" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nama Modul
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Contoh: HR System"
                                        />
                                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Key (Unique ID)
                                        </label>
                                        <input
                                            v-model="form.key"
                                            type="text"
                                            required
                                            :disabled="isEditing"
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                                            placeholder="cis_hr"
                                        />
                                        <p v-if="form.errors.key" class="mt-1 text-xs text-red-500">{{ form.errors.key }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Deskripsi
                                        </label>
                                        <textarea
                                            v-model="form.description"
                                            rows="2"
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            URL
                                        </label>
                                        <input
                                            v-model="form.url"
                                            type="url"
                                            required
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="https://hr.example.com"
                                        />
                                        <p v-if="form.errors.url" class="mt-1 text-xs text-red-500">{{ form.errors.url }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Warna Tema
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="color in colorOptions"
                                                :key="color"
                                                type="button"
                                                @click="form.color = color"
                                                :class="[
                                                    getColorClass(color),
                                                    form.color === color ? 'ring-2 ring-offset-2 ring-gray-400 dark:ring-offset-[#1a1a1a]' : '',
                                                    'w-8 h-8 rounded-lg transition-all'
                                                ]"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            id="is_active"
                                            class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                        />
                                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">
                                            Modul aktif
                                        </label>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-white/10 mt-4">
                                        <button
                                            type="button"
                                            @click="showFormModal = false"
                                            class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center"
                                        >
                                            <span v-if="form.processing" class="mr-2">...</span>
                                            {{ isEditing ? 'Simpan Perubahan' : 'Tambah Modul' }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Global Confirm Modal -->
        <ConfirmModal
            :show="confirmModal.show"
            :title="confirmModal.title"
            :message="confirmModal.message"
            :confirm-text="confirmModal.confirmText"
            :type="confirmModal.type"
            :loading="confirmModal.loading"
            @close="confirmModal.show = false"
            @confirm="handleConfirm"
        />
    </AdminLayout>
</template>
