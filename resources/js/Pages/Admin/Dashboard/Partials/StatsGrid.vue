<script setup>
const props = defineProps({
    stats: Object
});

const statCards = [
    { key: 'total_users', label: 'Total User', icon: 'users', color: 'blue' },
    { key: 'active_users', label: 'User Aktif', icon: 'check-circle', color: 'emerald' },
    { key: 'pending_users', label: 'Menunggu Approval', icon: 'clock', color: 'amber' },
    { key: 'admin_count', label: 'Admin', icon: 'shield', color: 'violet' },
];

const getIcon = (name) => {
    const icons = {
        'users': 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'clock': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'shield': 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    };
    return icons[name] || icons['users'];
};

const getColorClass = (color) => {
    const colors = {
        blue: 'bg-blue-500',
        emerald: 'bg-emerald-500',
        amber: 'bg-amber-500',
        violet: 'bg-violet-500',
    };
    return colors[color] || 'bg-gray-500';
};
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
            v-for="stat in statCards"
            :key="stat.key"
            class="bg-white dark:bg-[#1a1a1a] rounded-xl p-5 border border-gray-200/80 dark:border-white/5"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.label }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ stats[stat.key] || 0 }}
                    </p>
                </div>
                <div 
                    class="w-12 h-12 rounded-xl flex items-center justify-center"
                    :class="[getColorClass(stat.color), 'bg-opacity-10 dark:bg-opacity-20']"
                >
                    <svg 
                        class="w-6 h-6" 
                        :class="getColorClass(stat.color).replace('bg-', 'text-')"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getIcon(stat.icon)" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>
