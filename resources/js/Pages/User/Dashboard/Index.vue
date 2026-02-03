<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WelcomeHeader from './Partials/WelcomeHeader.vue';
import AppGrid from './Partials/AppGrid.vue';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    apps: Array,
});

const displayName = computed(() => {
    return props.user?.nama || props.user?.email?.split('@')[0] || 'User';
});
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <div class="space-y-8">
            <WelcomeHeader :display-name="displayName" />
            <AppGrid :apps="apps" />
        </div>
    </AuthenticatedLayout>
</template>
