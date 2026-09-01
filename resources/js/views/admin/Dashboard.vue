<template>
    <section class="admin-dashboard">
        <div class="admin-dashboard__welcome">
            <span class="admin-dashboard__icon" aria-hidden="true">
                <i class="bi bi-stars"></i>
            </span>
            <div>
                <span class="admin-dashboard__eyebrow">Dashboard</span>
                <h2>Welcome Admin<span v-if="adminName">, {{ adminName }}</span></h2>
                <p>Your administration workspace is ready.</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from "vue";
import adminAuth from "../../services/adminAuth";

const adminName = computed(() => adminAuth.getCurrentUser()?.name || "");
</script>

<style scoped>
.admin-dashboard__welcome {
    min-height: 235px;
    padding: clamp(1.5rem, 4vw, 2.5rem);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #ffffff;
    box-shadow: 0 12px 32px rgba(30, 41, 59, 0.06);
}

.admin-dashboard__icon {
    width: 64px;
    height: 64px;
    border-radius: 17px;
    color: #1d4ed8;
    background: #dbeafe;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    font-size: 1.45rem;
}

.admin-dashboard__eyebrow {
    color: #2563eb;
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.admin-dashboard h2 {
    margin: 0.35rem 0 0.45rem;
    color: #172033;
    font-size: clamp(1.55rem, 3vw, 2.2rem);
    font-weight: 780;
}

.admin-dashboard p {
    margin: 0;
    color: #64748b;
}

@media (max-width: 560px) {
    .admin-dashboard__welcome {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
