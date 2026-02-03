<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    selectedUsers: Array
});

const emit = defineEmits([
    'update:selectedUsers', 
    'toggle-select-all', 
    'toggle-role', 
    'open-modules', 
    'approve', 
    'reject', 
    'delete'
]);

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        rejected: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return badges[status] || badges.pending;
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const updateSelected = (id, checked) => {
    const newSelected = checked 
        ? [...props.selectedUsers, id]
        : props.selectedUsers.filter(uid => uid !== id);
    emit('update:selectedUsers', newSelected);
};
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input
                                type="checkbox"
                                :checked="selectedUsers?.length === users.data.length && users.data.length > 0"
                                @change="$emit('toggle-select-all')"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Modul</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Terdaftar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200/80 dark:divide-white/5">
                    <tr 
                        v-for="user in users.data" 
                        :key="user.id"
                        class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                    >
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="selectedUsers?.includes(user.id)"
                                @change="(e) => updateSelected(user.id, e.target.checked)"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-medium text-sm flex-shrink-0">
                                    {{ getInitials(user.name) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <button
                                @click="$emit('toggle-role', user)"
                                :class="[
                                    user.role === 'admin' 
                                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    'px-2 py-1 text-xs rounded-full font-medium hover:opacity-80 transition-opacity'
                                ]"
                            >
                                {{ user.role }}
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full font-medium" :class="getStatusBadge(user.approval_status)">
                                {{ getStatusLabel(user.approval_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button
                                @click="$emit('open-modules', user)"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                {{ user.modules_count || 0 }} modul
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(user.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end space-x-2">
                                <button
                                    v-if="user.approval_status === 'pending'"
                                    @click="$emit('approve', user)"
                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                    title="Approve"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button
                                    v-if="user.approval_status === 'pending'"
                                    @click="$emit('reject', user)"
                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Reject"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <button
                                    @click="$emit('delete', user)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Hapus"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="px-4 py-3 border-t border-gray-200/80 dark:border-white/5 flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ users.from }} - {{ users.to }} dari {{ users.total }} user
            </p>
            <div class="flex space-x-2">
                <Link
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                        link.active 
                            ? 'bg-blue-600 text-white' 
                            : link.url 
                                ? 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' 
                                : 'text-gray-300 dark:text-gray-600 cursor-not-allowed',
                        'px-3 py-1 rounded-lg text-sm'
                    ]"
                    v-html="link.label"
                    preserve-state
                />
            </div>
        </div>
    </div>
</template>
