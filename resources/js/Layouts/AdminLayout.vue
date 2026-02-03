<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Admin',
    },
});

// Dark mode
const isDark = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('darkMode');
    if (saved !== null) {
        isDark.value = saved === 'true';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
});

watch(isDark, () => {
    localStorage.setItem('darkMode', isDark.value.toString());
    applyTheme();
});

const applyTheme = () => {
    document.documentElement.classList.toggle('dark', isDark.value);
};

const toggleDark = () => {
    isDark.value = !isDark.value;
};

const logout = () => {
    router.post('/logout');
};

const user = computed(() => {
    return usePage().props.auth?.user || {};
});

const displayName = computed(() => {
    return user.value.name || user.value.email?.split('@')[0] || 'Admin';
});

const getInitials = (name) => {
    if (!name) return 'A';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const navItems = [
    { name: 'Dashboard', href: '/admin', icon: 'home' },
    { name: 'User Management', href: '/admin/users', icon: 'users' },
    { name: 'Modul', href: '/admin/modules', icon: 'grid' },
];

const getNavIcon = (name) => {
    const icons = {
        home: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        grid: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    };
    return icons[name] || icons.home;
};

const isActive = (href) => {
    const path = window.location.pathname;
    if (href === '/admin') {
        return path === '/admin' || path === '/admin/';
    }
    return path.startsWith(href);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f]">
        <Head :title="title + ' - Divusi SSO'" />

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-[#1a1a1a] border-r border-gray-200/80 dark:border-white/5 z-30 hidden lg:block">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200/80 dark:border-white/5">
                <Link href="/admin" class="flex items-center space-x-3">
                    <img src="/images/logo-divusi.png" alt="Divusi" class="h-8 w-auto" />
                    <span class="font-semibold text-gray-900 dark:text-white">Admin Panel</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        isActive(item.href)
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5',
                        'flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-colors'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getNavIcon(item.icon)" />
                    </svg>
                    <span class="font-medium text-sm">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Back to Portal -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200/80 dark:border-white/5">
                <Link
                    href="/dashboard"
                    class="flex items-center space-x-3 px-4 py-2.5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    <span class="font-medium text-sm">Kembali ke Portal</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Top Navbar -->
            <nav class="sticky top-0 z-20 h-16 bg-white/80 dark:bg-[#1a1a1a]/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-white/5">
                <div class="h-full px-4 sm:px-6 flex items-center justify-between">
                    <!-- Mobile menu button -->
                    <button class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white hidden lg:block">{{ title }}</h1>

                    <!-- Right side -->
                    <div class="flex items-center space-x-2">
                        <!-- Dark mode toggle -->
                        <button 
                            @click="toggleDark"
                            class="p-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                        >
                            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- User Menu -->
                        <Menu as="div" class="relative">
                            <MenuButton class="flex items-center space-x-3 p-1.5 pr-3 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center text-white text-sm font-medium">
                                    {{ getInitials(displayName) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">
                                    {{ displayName }}
                                </span>
                            </MenuButton>

                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1 focus:outline-none">
                                    <MenuItem v-slot="{ active }">
                                        <button 
                                            @click="logout"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Keluar
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
