<template>
    <div class="admin-login">
        <div class="admin-login__backdrop" aria-hidden="true"></div>

        <section class="admin-login__card" aria-labelledby="admin-login-title">
            <div class="admin-login__mark">
                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
            </div>

            <div class="admin-login__heading">
                <span>Secure administration</span>
                <h1 id="admin-login-title">Admin Login</h1>
                <p>Sign in with your administrator account to continue.</p>
            </div>

            <div v-if="errorMessage" class="admin-login__alert" role="alert">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                {{ errorMessage }}
            </div>

            <form novalidate @submit.prevent="submitLogin">
                <div class="admin-login__field">
                    <label for="admin-email">Email</label>
                    <div class="admin-login__input-wrap" :class="{ 'is-invalid': errors.email }">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input
                            id="admin-email"
                            v-model.trim="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="admin@example.com"
                            :disabled="loading"
                            @input="errors.email = ''"
                        />
                    </div>
                    <small v-if="errors.email" class="admin-login__error">{{ errors.email }}</small>
                </div>

                <div class="admin-login__field">
                    <label for="admin-password">Password</label>
                    <div class="admin-login__input-wrap" :class="{ 'is-invalid': errors.password }">
                        <i class="bi bi-key" aria-hidden="true"></i>
                        <input
                            id="admin-password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            :disabled="loading"
                            @input="errors.password = ''"
                        />
                    </div>
                    <small v-if="errors.password" class="admin-login__error">{{ errors.password }}</small>
                </div>

                <button type="submit" class="admin-login__submit" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <i v-else class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                    {{ loading ? "Signing in..." : "Login" }}
                </button>
            </form>

            <p class="admin-login__footer">
                <i class="bi bi-lock" aria-hidden="true"></i>
                Protected administrator access
            </p>
        </section>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import adminAuth from "../../services/adminAuth";

const router = useRouter();
const loading = ref(false);
const errorMessage = ref("");
const form = reactive({
    email: "",
    password: "",
});
const errors = reactive({
    email: "",
    password: "",
});

const validate = () => {
    errors.email = "";
    errors.password = "";

    if (! form.email) {
        errors.email = "Email is required.";
    } else if (! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = "Enter a valid email address.";
    }

    if (! form.password) {
        errors.password = "Password is required.";
    }

    return ! errors.email && ! errors.password;
};

const submitLogin = async () => {
    errorMessage.value = "";

    if (! validate()) {
        return;
    }

    loading.value = true;

    try {
        await adminAuth.login(form.email, form.password);
        await router.replace({ name: "admin.dashboard" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.email = error.response.data.errors?.email?.[0] || "";
            errors.password = error.response.data.errors?.password?.[0] || "";
        } else {
            errorMessage.value = error.response?.data?.message || "Unable to sign in. Please try again.";
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.admin-login {
    min-height: 100vh;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    display: grid;
    place-items: center;
    background: #f1f5f9;
}

.admin-login__backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.18), transparent 34%),
        radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.14), transparent 32%),
        linear-gradient(135deg, #f8fafc, #e8eef8);
}

.admin-login__card {
    width: min(100%, 430px);
    padding: 2.25rem;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(203, 213, 225, 0.8);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 24px 70px rgba(30, 41, 59, 0.14);
}

.admin-login__mark {
    width: 52px;
    height: 52px;
    margin: 0 auto 1.1rem;
    border-radius: 15px;
    display: grid;
    place-items: center;
    color: #ffffff;
    background: #2563eb;
    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
    font-size: 1.35rem;
}

.admin-login__heading {
    margin-bottom: 1.6rem;
    text-align: center;
}

.admin-login__heading span {
    color: #2563eb;
    font-size: 0.7rem;
    font-weight: 750;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.admin-login__heading h1 {
    margin: 0.35rem 0 0.45rem;
    color: #172033;
    font-size: 1.8rem;
    font-weight: 780;
}

.admin-login__heading p,
.admin-login__footer {
    color: #64748b;
    font-size: 0.86rem;
}

.admin-login__heading p {
    margin: 0;
}

.admin-login__alert {
    margin-bottom: 1rem;
    padding: 0.75rem 0.85rem;
    border: 1px solid #fecaca;
    border-radius: 9px;
    color: #b91c1c;
    background: #fef2f2;
    display: flex;
    gap: 0.55rem;
    font-size: 0.82rem;
}

.admin-login__field {
    margin-bottom: 1rem;
}

.admin-login__field label {
    margin-bottom: 0.4rem;
    color: #334155;
    display: block;
    font-size: 0.82rem;
    font-weight: 650;
}

.admin-login__input-wrap {
    height: 47px;
    padding: 0 0.85rem;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    background: #ffffff;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.admin-login__input-wrap:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.admin-login__input-wrap.is-invalid {
    border-color: #dc2626;
}

.admin-login__input-wrap i {
    color: #94a3b8;
}

.admin-login__input-wrap input {
    min-width: 0;
    height: 100%;
    border: 0;
    outline: 0;
    color: #172033;
    background: transparent;
    flex: 1;
    font-size: 0.9rem;
}

.admin-login__input-wrap input::placeholder {
    color: #94a3b8;
}

.admin-login__error {
    margin-top: 0.35rem;
    color: #dc2626;
    display: block;
    font-size: 0.75rem;
}

.admin-login__submit {
    width: 100%;
    min-height: 47px;
    margin-top: 0.35rem;
    border: 0;
    border-radius: 9px;
    color: #ffffff;
    background: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    transition: background-color 0.2s, transform 0.2s;
}

.admin-login__submit:hover:not(:disabled) {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.admin-login__submit:disabled {
    cursor: wait;
    opacity: 0.7;
}

.admin-login__footer {
    margin: 1.35rem 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.75rem;
}

@media (max-width: 520px) {
    .admin-login {
        padding: 1rem;
    }

    .admin-login__card {
        padding: 1.6rem 1.25rem;
    }
}
</style>
