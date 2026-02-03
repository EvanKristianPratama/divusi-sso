<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsGrid from './Partials/StatsGrid.vue';
import UserListCard from './Partials/UserListCard.vue';
import QuickActions from './Partials/QuickActions.vue';

defineProps({
    stats: Object,
    pendingUsers: Array,
    recentUsers: Array,
});
</script>

<template>
    <AdminLayout title="Admin Dashboard">
        <div class="space-y-6">
            <StatsGrid :stats="stats" />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pending Approvals -->
                <UserListCard
                    title="Menunggu Approval"
                    :users="pendingUsers"
                    view-all-link="/admin/users?approval_status=pending"
                    empty-message="Tidak ada user menunggu approval"
                />

                <!-- Recent Users -->
                <UserListCard
                    title="User Terbaru"
                    :users="recentUsers"
                    view-all-link="/admin/users"
                    empty-message="Belum ada user"
                />
            </div>

            <QuickActions />
        </div>
    </AdminLayout>
</template>
