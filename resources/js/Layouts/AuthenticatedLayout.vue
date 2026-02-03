<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

const props = defineProps({
    user: Object,
    title: {
        type: String,
        default: 'Dashboard',
    },
});

// Dark mode state
const isDark = ref(false);

onMounted(() => {
    // Check localStorage or system preference
    const saved = localStorage.getItem('darkMode');
    if (saved !== null) {
        isDark.value = saved === 'true';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
});

watch(isDark, () => {
    localStorage.setItem('darkMode', isDark.value);
    applyTheme();
});

const applyTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
};

const logout = () => {
    router.post('/logout');
};

const page = usePage();
const displayName = computed(() => {
    const user = page.props.auth?.user || props.user;
    return user?.name || user?.email?.split('@')[0] || 'User';
});

const getInitials = (name) => {
    if (!name) return 'U';
    return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] transition-colors duration-300">
        <Head :title="title" />
        
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-[#1a1a1a]/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-white/5">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="/dashboard" class="flex items-center space-x-2">
                        <img src="/images/logo-divusi.png" alt="Divusi Logo" class="h-10 w-auto" />
                    </a>

                    <!-- Right -->
                    <div class="flex items-center space-x-2">
                        <!-- Dark mode toggle -->
                        <button 
                            @click="toggleDarkMode"
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
                            <MenuButton class="flex items-center space-x-3 p-1.5 pr-3 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-colors focus:outline-none">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-sm font-medium">
                                    {{ getInitials(displayName) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:block">{{ displayName }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </MenuButton>

                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1 focus:outline-none">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ displayName }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user?.email }}</p>
                                    </div>
                                    
                                    <MenuItem v-if="$page.props.auth?.user?.is_admin" v-slot="{ active }">
                                        <a 
                                            href="/admin"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Admin Panel
                                        </a>
                                    </MenuItem>
                                    
                                    <MenuItem v-slot="{ active }">
                                        <a 
                                            href="/profile"
                                            :class="[active ? 'bg-gray-50 dark:bg-white/5' : '', 'flex items-center w-full px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300']"
                                        >
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Profil Saya
                                        </a>
                                    </MenuItem>

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
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-8">
            <div class="pt-8 border-t border-gray-200/50 dark:border-white/5">
                <p class="text-center text-sm text-gray-400 dark:text-gray-500">
                    © {{ new Date().getFullYear() }} Divusi. Single Sign-On Platform.
                </p>
            </div>
        </div>
    </div>
</template>
