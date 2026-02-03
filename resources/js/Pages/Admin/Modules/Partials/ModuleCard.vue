<script setup>
defineProps({
    mod: Object
});

defineEmits(['toggle', 'edit', 'delete']);

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

const ICONS = {
    'shield-check': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    'clipboard-document-check': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    'users': 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    'currency-dollar': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'squares-2x2': 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
};

const getColorClass = (color) => COLORS[color] || COLORS['gray'];
const getIcon = (name) => ICONS[name] || ICONS['squares-2x2'];
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div 
                    class="w-12 h-12 rounded-xl flex items-center justify-center"
                    :class="[getColorClass(mod.color), 'bg-opacity-10 dark:bg-opacity-20']"
                >
                    <svg 
                        class="w-6 h-6" 
                        :class="getColorClass(mod.color).replace('bg-', 'text-')"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getIcon(mod.icon)" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ mod.name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ mod.key }}</p>
                </div>
            </div>
            <button
                @click="$emit('toggle', mod)"
                :class="[
                    mod.is_active 
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                    'px-2 py-1 text-xs rounded-full font-medium transition-colors'
                ]"
            >
                {{ mod.is_active ? 'Aktif' : 'Nonaktif' }}
            </button>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
            {{ mod.description || 'Tidak ada deskripsi' }}
        </p>

        <div class="flex items-center justify-between text-sm">
            <a 
                :href="mod.url" 
                target="_blank"
                class="text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[200px]"
            >
                {{ mod.url }}
            </a>
            <span class="text-gray-500 dark:text-gray-400">
                {{ mod.users_count || 0 }} user
            </span>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-200/80 dark:border-white/5 flex justify-end">
            <button
                @click="$emit('edit', mod)"
                class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
                Edit
            </button>
            <button
                @click="$emit('delete', mod)"
                class="ml-3 text-sm text-red-600 dark:text-red-400 hover:underline"
            >
                Hapus
            </button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
