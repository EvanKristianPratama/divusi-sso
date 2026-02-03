<script setup>
defineProps({
    title: String,
    subtitle: String,
});
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ subtitle }}
            </p>
        </div>
        <div class="flex gap-3">
            <slot name="actions" />
        </div>
    </div>
</template>
