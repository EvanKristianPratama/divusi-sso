<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import EmptyState from '@/Components/EmptyState.vue';
import UserTable from './Partials/UserTable.vue';
import UserFilters from './Partials/UserFilters.vue';

const props = defineProps({
    users: Object, // Paginated
    modules: Array,
    filters: Object,
    stats: Object,
});

// Local state
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.approval_status || '');
const roleFilter = ref(props.filters?.role || '');
const selectedUsers = ref([]);
const showAddModal = ref(false);
const showModulesModal = ref(false);
const selectedUser = ref(null);

// Modal State
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

// Form untuk pre-register
const addForm = ref({
    email: '',
    role: 'user',
    module_ids: [],
});

// Debounced search
let searchTimeout = null;
watch([search, statusFilter, roleFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get('/admin/users', {
        search: search.value || undefined,
        approval_status: statusFilter.value || undefined,
        role: roleFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    roleFilter.value = '';
    router.get('/admin/users');
};

// Actions
const approveUser = (user) => {
    openConfirmFn({
        title: 'Approve User',
        message: `Apakah Anda yakin ingin menyetujui user "${user.name}"? User akan dapat login ke sistem.`,
        confirmText: 'Approve User',
        type: 'success',
        action: (options) => router.post(`/admin/users/${user.id}/approve`, {}, {
            preserveScroll: true,
            ...options
        })
    });
};

const rejectUser = (user) => {
    openConfirmFn({
        title: 'Reject User',
        message: `Apakah Anda yakin ingin menolak user "${user.name}"?`,
        confirmText: 'Reject User',
        type: 'danger',
        action: (options) => router.post(`/admin/users/${user.id}/reject`, {}, {
            preserveScroll: true,
            ...options
        })
    });
};

const deleteUser = (user) => {
    openConfirmFn({
        title: 'Hapus User',
        message: `Apakah Anda yakin ingin menghapus user "${user.name}"? Aksi ini tidak bisa dibatalkan dan semua data terkait akan hilang.`,
        confirmText: 'Hapus Permanen',
        type: 'danger',
        action: (options) => router.delete(`/admin/users/${user.id}`, {
            preserveScroll: true,
            ...options
        })
    });
};

const bulkApprove = () => {
    if (selectedUsers.value.length === 0) return;
    openConfirmFn({
        title: 'Approve Banyak User',
        message: `Apakah Anda yakin ingin menyetujui ${selectedUsers.value.length} user yang dipilih?`,
        confirmText: `Approve ${selectedUsers.value.length} User`,
        type: 'success',
        action: (options) => router.post('/admin/users/bulk-approve', {
            user_ids: selectedUsers.value,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
            ...options
        })
    });
};

const bulkReject = () => {
    if (selectedUsers.value.length === 0) return;
    openConfirmFn({
        title: 'Reject Banyak User',
        message: `Apakah Anda yakin ingin menolak ${selectedUsers.value.length} user yang dipilih?`,
        confirmText: `Reject ${selectedUsers.value.length} User`,
        type: 'danger',
        action: (options) => router.post('/admin/users/bulk-reject', {
            user_ids: selectedUsers.value,
        }, {
            onSuccess: () => {
                selectedUsers.value = [];
            },
            ...options
        })
    });
};

const toggleSelectAll = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(u => u.id);
    }
};

const openModulesModal = (user) => {
    selectedUser.value = {
        ...user,
        module_ids: user.modules?.map(m => m.id) || [],
    };
    showModulesModal.value = true;
};

const saveModules = () => {
    router.post(`/admin/users/${selectedUser.value.id}/modules`, {
        module_ids: selectedUser.value.module_ids,
    }, {
        onSuccess: () => {
            showModulesModal.value = false;
            selectedUser.value = null;
        }
    });
};

const submitAddUser = () => {
    router.post('/admin/users/pre-register', addForm.value, {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.value = { email: '', role: 'user', module_ids: [] };
        }
    });
};

const toggleRole = (user) => {
    const newRole = user.role === 'admin' ? 'user' : 'admin';
    openConfirmFn({
        title: 'Ubah Role User',
        message: `Ubah role user "${user.name}" menjadi ${newRole === 'admin' ? 'Admin' : 'User Biasa'}?`,
        confirmText: 'Ubah Role',
        type: 'warning',
        action: (options) => router.put(`/admin/users/${user.id}`, { role: newRole }, {
             preserveScroll: true,
             ...options
        })
    });
};
</script>

<template>
    <AdminLayout title="User Management">
        <div class="space-y-6">
            <!-- Header -->
            <SectionHeader 
                title="User Management" 
                subtitle="Kelola user dan akses modul"
            >
                <template #actions>
                    <button
                        @click="showAddModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah User
                    </button>
                </template>
            </SectionHeader>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="(value, label) in {
                    'Total': stats.total_users,
                    'Aktif': stats.active_users,
                    'Pending': stats.pending_users,
                    'Admin': stats.admin_count
                }" :key="label" class="bg-white dark:bg-[#1a1a1a] rounded-xl p-4 border border-gray-200/80 dark:border-white/5">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ label }}</p>
                    <p class="text-xl font-bold" :class="{
                        'text-gray-900 dark:text-white': label === 'Total',
                        'text-emerald-600': label === 'Aktif',
                        'text-amber-600': label === 'Pending',
                        'text-violet-600': label === 'Admin'
                    }">{{ value }}</p>
                </div>
            </div>

            <!-- Filters -->
            <UserFilters
                v-model:search="search"
                v-model:statusFilter="statusFilter"
                v-model:roleFilter="roleFilter"
                :selected-count="selectedUsers.length"
                @reset="clearFilters"
                @bulk-approve="bulkApprove"
                @bulk-reject="bulkReject"
            />

            <!-- DATA FOUND -->
            <UserTable 
                v-if="users.data.length > 0"
                :users="users"
                v-model:selectedUsers="selectedUsers"
                @toggle-select-all="toggleSelectAll"
                @toggle-role="toggleRole"
                @open-modules="openModulesModal"
                @approve="approveUser"
                @reject="rejectUser"
                @delete="deleteUser"
            />

            <!-- EMPTY STATE -->
            <EmptyState 
                v-else 
                message="Tidak ada user ditemukan"
            />
        </div>

        <!-- Add User Modal -->
        <TransitionRoot appear :show="showAddModal" as="template">
            <Dialog as="div" @close="showAddModal = false" class="relative z-50">
                <TransitionChild
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50" />
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
                                    Tambah User Baru
                                </DialogTitle>

                                <form @submit.prevent="submitAddUser" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Email
                                        </label>
                                        <input
                                            v-model="addForm.email"
                                            type="email"
                                            required
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="user@example.com"
                                        />
                                        <p class="text-xs text-gray-500 mt-1">User akan bisa login saat mendaftar dengan email ini</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Role
                                        </label>
                                        <select
                                            v-model="addForm.role"
                                            class="w-full px-4 py-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Akses Modul
                                        </label>
                                        <div class="space-y-2 max-h-40 overflow-y-auto">
                                            <label
                                                v-for="mod in modules"
                                                :key="mod.id"
                                                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :value="mod.id"
                                                    v-model="addForm.module_ids"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                                />
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ mod.name }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4">
                                        <button
                                            type="button"
                                            @click="showAddModal = false"
                                            class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                        >
                                            Tambah User
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Modules Assignment Modal -->
        <TransitionRoot appear :show="showModulesModal" as="template">
            <Dialog as="div" @close="showModulesModal = false" class="relative z-50">
                <TransitionChild
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/50" />
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
                                    Akses Modul - {{ selectedUser?.name }}
                                </DialogTitle>

                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    <label
                                        v-for="mod in modules"
                                        :key="mod.id"
                                        class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer border border-gray-200 dark:border-white/10"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <input
                                                type="checkbox"
                                                :value="mod.id"
                                                v-model="selectedUser.module_ids"
                                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                            />
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ mod.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ mod.description }}</p>
                                            </div>
                                        </div>
                                        <span 
                                            v-if="mod.is_active"
                                            class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            Aktif
                                        </span>
                                    </label>
                                </div>

                                <div class="flex justify-end space-x-3 pt-4 mt-4 border-t border-gray-200 dark:border-white/10">
                                    <button
                                        type="button"
                                        @click="showModulesModal = false"
                                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        @click="saveModules"
                                        class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                    >
                                        Simpan
                                    </button>
                                </div>
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
