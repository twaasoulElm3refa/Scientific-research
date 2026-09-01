<template>
    <div class="min-h-screen w-full relative flex justify-center items-center overflow-hidden">
        <!-- Background -->
        <img src="/images/overlay.png" class="fixed inset-0 w-full h-full object-cover" />

        <!-- Card -->
        <transition name="fade-scale">
            <div class="relative z-10 max-w-md w-full backdrop-blur-sm p-8 rounded-2xl shadow-xl border border-gray-100"
                style="background-color: rgba(255,255,255,0.75);">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
                    إنشاء حساب
                </h2>

                <p class="text-center text-gray-500 mb-6 text-sm">
                    أنشئ حسابك وابدأ رحلتك في دعم عطاء
                </p>

                <!-- Success Message -->
                <transition name="fade">
                    <p v-if="successMessage" class="text-center text-green-600 text-sm mb-4">
                        {{ successMessage }}
                    </p>
                </transition>

                <form @submit.prevent="handleRegister" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            الاسم
                        </label>
                        <input v-model="form.name" type="text" required placeholder="الاسم بالكامل"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 outline-none" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            البريد الإلكتروني
                        </label>
                        <input v-model="form.email" type="email" required placeholder="البريد الإلكتروني"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            كلمة المرور
                        </label>

                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required
                                placeholder="كلمة المرور"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 outline-none pr-12" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                                {{ showPassword ? '🙈' : '👁️' }}
                            </button>
                        </div>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            تأكيد كلمة المرور
                        </label>

                        <div class="relative">
                            <input v-model="form.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'" required
                                placeholder="تأكيد كلمة المرور"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 outline-none pr-12" />

                            <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                                {{ showPasswordConfirmation ? '🙈' : '👁️' }}
                            </button>
                        </div>
                    </div>


                    <!-- Register Button -->
                    <button type="submit"
                        class="w-full bg-green-300 text-gray-800 font-semibold py-3 rounded-lg shadow-md hover:bg-green-400 transition"
                        :disabled="loading">
                        {{ loading ? 'جاري إنشاء الحساب...' : 'إنشاء حساب' }}
                    </button>

                    <!-- Login Link -->
                    <div class="mt-4 text-center">
                        <span class="text-gray-600 text-sm">
                            لديك حساب بالفعل؟
                        </span>

                        <button type="button" @click="goToLogin"
                            class="inline-flex items-center gap-2 text-green-600 font-medium mt-2">
                            تسجيل الدخول
                            <span class="arrow">➜</span>
                        </button>
                    </div>

                    <!-- Error -->
                    <p v-if="error" class="text-center text-red-500 text-sm mt-3">
                        {{ error }}
                    </p>
                </form>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const router = useRouter()

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const loading = ref(false)
const error = ref('')
const successMessage = ref('')

const handleRegister = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await axios.post('/v1/users/register', form.value)

        if (response.data.status === 'success') {
            const token = response.data.data.token

            // ✅ نفس لوجيك login
            localStorage.setItem('auth_token', token)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

            successMessage.value = 'تم إنشاء الحساب بنجاح 🎉'
            router.push('/')
        } else {
            error.value = response.data.message || 'فشل إنشاء الحساب'
        }
    } catch (err) {
        if (err.response?.data?.errors) {
            error.value = Object.values(err.response.data.errors).flat().join(' - ')
        } else {
            error.value = 'حدث خطأ أثناء إنشاء الحساب'
        }
    } finally {
        loading.value = false
    }
}


const goToLogin = () => {
    router.push('/login')
}
</script>

<style scoped>
/* Animations */
.fade-scale-enter-active {
    transition: all 0.4s ease;
}

.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Arrow */
.arrow {
    display: inline-block;
    animation: arrowMove 0.6s ease-in-out infinite alternate;
}

@keyframes arrowMove {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(6px);
    }
}
</style>
