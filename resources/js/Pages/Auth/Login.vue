<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { initializeApp } from 'firebase/app';
import { 
    getAuth, 
    signInWithPopup,
    GoogleAuthProvider,
    GithubAuthProvider,
    OAuthProvider 
} from 'firebase/auth';
import { usePage } from '@inertiajs/vue3';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { watch } from 'vue';

const props = defineProps({
    firebaseConfig: Object,
    error: String,
});

const isLoading = ref(false);
const loadingProvider = ref(null);
const errorMessage = ref(props.error || '');
const showPendingModal = ref(false);
const page = usePage();

watch(() => page.props.flash?.status, (status) => {
    if (status === 'pending') {
        showPendingModal.value = true;
    }
}, { immediate: true });

watch(() => page.props.flash?.error, (err) => {
    if (err) errorMessage.value = err;
}, { immediate: true });

// Carousel images
const carouselImages = [
    {
        url: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&q=80',
        text: '"Satu akun untuk mengakses semua modul enterprise. Lebih aman, lebih cepat, lebih efisien."',
        subtext: 'Divusi Platform - Enterprise Solutions'
    },
    {
        url: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=1200&q=80',
        text: '"Kolaborasi tanpa batas dengan integrasi sistem yang mulus. Tingkatkan produktivitas tim Anda."',
        subtext: 'Integrated Workflow - Seamless Connection'
    },
    {
        url: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&q=80',
        text: '"Keamanan data prioritas utama. Akses terpusat dengan standar enterprise."',
        subtext: 'Secure Access - Enterprise Standard'
    },
];
const currentImageIndex = ref(0);
let carouselInterval = null;

// Initialize Firebase
let auth = null;
if (props.firebaseConfig) {
    const app = initializeApp(props.firebaseConfig);
    auth = getAuth(app);
}

onMounted(() => {
    carouselInterval = setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % carouselImages.length;
    }, 5000);
});

onUnmounted(() => {
    if (carouselInterval) clearInterval(carouselInterval);
});

const providers = {
    google: () => new GoogleAuthProvider(),
};

const loginWith = async (providerName) => {
    if (!auth) {
        errorMessage.value = 'Firebase tidak dikonfigurasi';
        return;
    }

    isLoading.value = true;
    loadingProvider.value = providerName;
    errorMessage.value = '';

    try {
        const provider = providers[providerName]();
        const result = await signInWithPopup(auth, provider);
        const idToken = await result.user.getIdToken();

        // Use window.location for full page navigation to ensure session is set
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/auth/firebase/callback';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
        form.appendChild(csrfInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'id_token';
        tokenInput.value = idToken;
        form.appendChild(tokenInput);
        
        const providerInput = document.createElement('input');
        providerInput.type = 'hidden';
        providerInput.name = 'provider';
        providerInput.value = providerName;
        form.appendChild(providerInput);
        
        document.body.appendChild(form);
        form.submit();
        
    } catch (error) {
        console.error('Login error:', error);
        if (error.code === 'auth/popup-closed-by-user') {
            errorMessage.value = 'Login dibatalkan';
        } else if (error.code === 'auth/account-exists-with-different-credential') {
            errorMessage.value = 'Email sudah terdaftar dengan provider lain';
        } else if (error.code === 'auth/popup-blocked') {
            errorMessage.value = 'Popup diblokir. Izinkan popup untuk login.';
        } else {
            errorMessage.value = error.message || 'Terjadi kesalahan saat login';
        }
        isLoading.value = false;
        loadingProvider.value = null;
    }
};

const socialButtons = [
    { 
        provider: 'google', 
        name: 'Google',
        bgClass: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200',
        icon: `<svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>`
    },
];
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="mb-10 flex flex-col items-center text-center">
                    <img src="/images/logo-divusi.png" alt="Divusi Logo" class="h-24 w-auto object-contain mb-4" />
                    <p class="text-gray-400 font-medium tracking-widest text-xs uppercase">Single Sign-On Platform</p>
                </div>

                <!-- Welcome Text -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Selamat Datang</h1>
                    <p class="text-gray-500 mt-2">Masuk dengan akun sosial media Anda untuk mengakses semua modul Divusi.</p>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl">
                    <p class="text-sm text-red-600 flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ errorMessage }}
                    </p>
                </div>

                <!-- Social Login Buttons -->
                <div class="space-y-3">
                    <button
                        v-for="btn in socialButtons"
                        :key="btn.provider"
                        @click="loginWith(btn.provider)"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center px-4 py-3.5 rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        :class="btn.bgClass"
                    >
                        <span v-if="loadingProvider === btn.provider" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span v-else class="flex items-center">
                            <span v-html="btn.icon" class="mr-3"></span>
                            Lanjutkan dengan {{ btn.name }}
                        </span>
                    </button>
                </div>

                <!-- Divider -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-xs text-gray-400">
                        Dengan melanjutkan, Anda menyetujui
                        <a href="#" class="text-blue-500 hover:underline">Ketentuan Layanan</a>
                        dan
                        <a href="#" class="text-blue-500 hover:underline">Kebijakan Privasi</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Image Carousel -->
        <div class="hidden lg:block lg:w-1/2 relative bg-gray-900">
            <!-- Background Images -->
            <div 
                v-for="(image, index) in carouselImages"
                :key="index"
                class="absolute inset-0 transition-opacity duration-[2000ms] ease-out"
                :class="currentImageIndex === index ? 'opacity-100' : 'opacity-0'"
            >
                <img :src="image.url" alt="" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-gray-900/50 to-gray-900/80"></div>
            </div>

            <!-- Content Overlay -->
            <div class="relative z-10 flex flex-col justify-between h-full p-12">
                <div></div>
                
                <!-- Quote Card -->
                <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 transition-all duration-500">
                    <Transition mode="out-in" name="fade">
                        <div :key="currentImageIndex" class="space-y-6">
                            <p class="text-white text-xl font-light leading-relaxed">
                                {{ carouselImages[currentImageIndex].text }}
                            </p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                    D
                                </div>
                                <div class="ml-4">
                                    <p class="text-white font-semibold">Divusi Platform</p>
                                    <p class="text-gray-300 text-sm">{{ carouselImages[currentImageIndex].subtext }}</p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Carousel Indicators -->
                <div class="flex justify-center space-x-2 mt-8">
                    <button
                        v-for="(_, index) in carouselImages"
                        :key="index"
                        @click="currentImageIndex = index"
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="currentImageIndex === index ? 'bg-white w-8' : 'bg-white/40 hover:bg-white/60'"
                    ></button>
                </div>
            </div>
        </div>
    </div>

    <TransitionRoot appear :show="showPendingModal" as="template">
        <Dialog as="div" @close="showPendingModal = false" class="relative z-50">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/25 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all border border-gray-100">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mb-4 text-yellow-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <DialogTitle as="h3" class="text-xl font-bold leading-6 text-gray-900 mb-2">
                                    Menunggu Persetujuan
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Akun Anda telah berhasil dibuat, namun saat ini statusnya masih <strong>Pending</strong>. Silakan hubungi Administrator untuk persetujuan akun agar dapat mengakses sistem.
                                    </p>
                                </div>
                                <div class="mt-6 w-full">
                                    <button type="button" class="inline-flex w-full justify-center rounded-xl border border-transparent bg-emerald-500 px-4 py-3 text-sm font-medium text-white hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all" @click="showPendingModal = false">
                                        Mengerti
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Fade Transition for Text */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
