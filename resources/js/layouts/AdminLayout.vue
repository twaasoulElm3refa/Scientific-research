<template>
    <div class="admin-shell">
        <AdminSidebar />

        <div class="admin-shell__workspace">
            <AdminHeader />

            <main class="admin-shell__content">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import AdminHeader from "../components/admin/AdminHeader.vue";
import AdminSidebar from "../components/admin/AdminSidebar.vue";
</script>

<style scoped>
.admin-shell {
    min-height: 100vh;
    display: flex;
    background: #f4f7fb;
}

.admin-shell__workspace {
    min-width: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.admin-shell__content {
    padding: 1.75rem;
    flex: 1;
}

@media (max-width: 640px) {
    .admin-shell__content {
        padding: 1rem;
    }
}
</style>
