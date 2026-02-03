<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionHeader from '@/Components/SectionHeader.vue';

defineProps({
    user: Object
});
</script>

<template>
    <AuthenticatedLayout title="Profil Saya">
        <div class="max-w-2xl mx-auto space-y-6">
            <SectionHeader 
                title="Profil Saya" 
                subtitle="Informasi akun Anda" 
            />

            <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-6 border border-gray-200/80 dark:border-white/5">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold">
                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ user.name || 'User' }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                        <span class="inline-flex mt-2 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 capitalize">
                            {{ user.role || 'user' }}
                        </span>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-gray-100 dark:border-white/5">
                     <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Detail Akun</h3>
                     <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ user.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Bergabung Sejak</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                                {{ new Date(user.created_at || Date.now()).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                            </dd>
                        </div>
                     </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
