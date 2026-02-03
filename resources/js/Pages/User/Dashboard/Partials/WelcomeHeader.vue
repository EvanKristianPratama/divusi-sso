<script setup>
import { computed } from 'vue';

const props = defineProps({
    displayName: String
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 5) return 'Selamat Malam';
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('id-ID', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
});
</script>

<template>
    <div class="mb-8">
        <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400 text-sm mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ currentDate }}</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            {{ greeting }}, {{ displayName }} 👋
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Pilih modul untuk memulai</p>
    </div>
</template>
