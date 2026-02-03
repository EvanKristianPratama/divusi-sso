<script setup>
defineProps({
    title: {
        type: String,
        default: 'Data tidak ditemukan'
    },
    message: String,
    action: String
});

defineEmits(['action']);
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl border border-gray-200/80 dark:border-white/5 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </slot>
            </svg>
        </div>
        <p class="text-gray-900 dark:text-white font-medium mb-1" v-if="title">{{ title }}</p>
        <p class="text-gray-500 dark:text-gray-400 mb-4" v-if="message">{{ message }}</p>
        <button
            v-if="action"
            @click="$emit('action')"
            class="text-blue-600 dark:text-blue-400 hover:underline text-sm"
        >
            {{ action }}
        </button>
    </div>
</template>
