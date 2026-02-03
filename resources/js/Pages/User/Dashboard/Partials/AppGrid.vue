<script setup>
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    apps: {
        type: Array,
        default: () => []
    }
});

const getAppIcon = (key) => {
    const icons = {
        cobit: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        pmo: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        hr: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        finance: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return icons[key] || 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z';
};

const getAppColor = (app) => {
    const colors = {
        emerald: 'bg-emerald-500',
        blue: 'bg-blue-500',
        violet: 'bg-violet-500',
        amber: 'bg-amber-500',
        red: 'bg-red-500',
        indigo: 'bg-indigo-500',
        pink: 'bg-pink-500',
        cyan: 'bg-cyan-500',
    };
    return colors[app.color] || colors[app.key] || 'bg-gray-500';
};
</script>

<template>
    <!-- Apps Grid -->
    <div v-if="apps && apps.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a
            v-for="app in apps"
            :key="app.key"
            :href="app.url"
            target="_blank"
            rel="noopener noreferrer"
            class="group relative bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 border border-gray-200/80 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300 hover:shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-black/20 hover:-translate-y-0.5"
        >
            <!-- Color accent bar -->
            <div 
                class="absolute top-0 left-6 right-6 h-1 rounded-b-full opacity-0 group-hover:opacity-100 transition-opacity" 
                :class="getAppColor(app)"
            ></div>
            
            <div class="flex items-start justify-between">
                <div 
                    class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors" 
                    :class="[getAppColor(app), 'bg-opacity-10 dark:bg-opacity-20']"
                >
                    <svg 
                        class="w-6 h-6 transition-colors" 
                        :class="getAppColor(app).replace('bg-', 'text-')" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getAppIcon(app.key)" />
                    </svg>
                </div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">
                    {{ app.name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ app.description || 'Akses modul ' + app.name }}
                </p>
            </div>

            <!-- Status badge -->
            <div class="mt-4 flex items-center">
                <span class="inline-flex items-center text-xs font-medium text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Tersedia
                </span>
            </div>
        </a>
    </div>

    <!-- Empty State -->
    <EmptyState 
        v-else 
        message="Belum ada modul" 
        sub-message="Modul akan segera tersedia untuk Anda"
    >
         <template #icon>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </template>
    </EmptyState>
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
