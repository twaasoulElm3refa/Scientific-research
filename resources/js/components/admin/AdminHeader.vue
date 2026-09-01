<template>
    <header class="admin-header">
        <div>
            <span class="admin-header__eyebrow">Administration</span>
            <h1>Admin Panel</h1>
        </div>

        <div class="admin-header__account">
            <div class="admin-header__identity">
                <span class="admin-header__avatar" aria-hidden="true">
                    {{ adminInitial }}
                </span>
                <div>
                    <strong>{{ adminName }}</strong>
                    <small>Administrator</small>
                </div>
            </div>

            <button
                type="button"
                class="admin-header__logout"
                :disabled="loggingOut"
                @click="handleLogout"
            >
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                {{ loggingOut ? "Logging out..." : "Logout" }}
            </button>
        </div>
    </header>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import adminAuth from "../../services/adminAuth";

const router = useRouter();
const loggingOut = ref(false);
const admin = ref(adminAuth.getCurrentUser());

const adminName = computed(() => admin.value?.name || "Admin");
const adminInitial = computed(() => adminName.value.charAt(0).toUpperCase());

const handleLogout = async () => {
    loggingOut.value = true;

    try {
        await adminAuth.logout();
    } catch (error) {
        console.warn("The server logout request failed; local admin access was cleared.", error);
    } finally {
        adminAuth.clearAuth();
        loggingOut.value = false;
        await router.replace({ name: "admin.login" });
    }
};
</script>

<style scoped>
.admin-header {
    min-height: 82px;
    padding: 1rem 1.5rem;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.admin-header__eyebrow {
    display: block;
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.admin-header h1 {
    margin: 0.1rem 0 0;
    color: #172033;
    font-size: 1.35rem;
    font-weight: 750;
}

.admin-header__account,
.admin-header__identity {
    display: flex;
    align-items: center;
}

.admin-header__account {
    gap: 1.25rem;
}

.admin-header__identity {
    gap: 0.65rem;
}

.admin-header__identity strong,
.admin-header__identity small {
    display: block;
}

.admin-header__identity strong {
    color: #172033;
    font-size: 0.9rem;
}

.admin-header__identity small {
    color: #64748b;
    font-size: 0.75rem;
}

.admin-header__avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    color: #ffffff;
    background: #2563eb;
    font-weight: 700;
}

.admin-header__logout {
    padding: 0.55rem 0.85rem;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
    color: #334155;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.85rem;
    font-weight: 650;
    transition: background-color 0.2s, border-color 0.2s;
}

.admin-header__logout:hover:not(:disabled) {
    background: #f8fafc;
    border-color: #b8c4d1;
}

.admin-header__logout:disabled {
    cursor: wait;
    opacity: 0.65;
}

@media (max-width: 640px) {
    .admin-header {
        align-items: flex-start;
        padding: 1rem;
    }

    .admin-header__identity {
        display: none;
    }
}
</style>
