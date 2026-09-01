<template>
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <span class="admin-sidebar__brand-mark">SP</span>
            <div>
                <strong>Search Project</strong>
                <small>Management</small>
            </div>
        </div>

        <nav aria-label="Admin navigation">
            <span class="admin-sidebar__label">Workspace</span>
            <RouterLink :to="{ name: 'admin.dashboard' }" class="admin-sidebar__link">
                <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
                <span>Dashboard</span>
            </RouterLink>

            <button
                type="button"
                class="admin-sidebar__link admin-sidebar__toggle"
                :class="{ 'router-link-active': isDocumentsRoute }"
                :aria-expanded="documentsOpen"
                @click="documentsOpen = !documentsOpen"
            >
                <i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i>
                <span>Documents</span>
                <i
                    class="bi admin-sidebar__chevron"
                    :class="documentsOpen ? 'bi-chevron-up' : 'bi-chevron-down'"
                    aria-hidden="true"
                ></i>
            </button>

            <div v-show="documentsOpen" class="admin-sidebar__submenu">
                <RouterLink :to="{ name: 'admin.documents.create' }" class="admin-sidebar__sublink">
                    Add Document
                </RouterLink>
                <RouterLink :to="{ name: 'admin.documents.index' }" class="admin-sidebar__sublink">
                    All Documents
                </RouterLink>
            </div>

            <RouterLink :to="{ name: 'admin.settings.google-drive' }" class="admin-sidebar__link">
                <i class="bi bi-cloud-check-fill" aria-hidden="true"></i>
                <span>Google Drive</span>
            </RouterLink>
        </nav>
    </aside>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const isDocumentsRoute = computed(() => route.path.startsWith("/admin/documents"));
const documentsOpen = ref(isDocumentsRoute.value);
</script>

<style scoped>
.admin-sidebar {
    width: 250px;
    flex: 0 0 250px;
    min-height: 100%;
    padding: 1.35rem 1rem;
    color: #cbd5e1;
    background: #111827;
}

.admin-sidebar__brand {
    margin-bottom: 2.25rem;
    padding: 0 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-sidebar__brand-mark {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    color: #ffffff;
    background: #2563eb;
    font-size: 0.85rem;
    font-weight: 800;
}

.admin-sidebar__brand strong,
.admin-sidebar__brand small {
    display: block;
}

.admin-sidebar__brand strong {
    color: #ffffff;
    font-size: 0.95rem;
}

.admin-sidebar__brand small {
    color: #94a3b8;
    font-size: 0.75rem;
}

.admin-sidebar__label {
    display: block;
    margin: 0 0.65rem 0.55rem;
    color: #64748b;
    font-size: 0.67rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.admin-sidebar__link {
    width: 100%;
    padding: 0.72rem 0.8rem;
    border-radius: 9px;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    border: 0;
    transition: background-color 0.2s, color 0.2s;
}

.admin-sidebar__link:hover,
.admin-sidebar__link.router-link-exact-active,
.admin-sidebar__link.router-link-active {
    color: #ffffff;
    background: #263449;
}

.admin-sidebar__toggle {
    margin-top: 0.25rem;
    text-align: left;
}

.admin-sidebar__chevron {
    margin-left: auto;
    font-size: 0.7rem;
}

.admin-sidebar__submenu {
    margin: 0.25rem 0 0.5rem 2.2rem;
    padding-left: 0.75rem;
    border-left: 1px solid #334155;
}

.admin-sidebar__sublink {
    padding: 0.5rem 0.65rem;
    border-radius: 7px;
    color: #94a3b8;
    display: block;
    font-size: 0.8rem;
    font-weight: 550;
    text-decoration: none;
}

.admin-sidebar__sublink:hover,
.admin-sidebar__sublink.router-link-exact-active {
    color: #ffffff;
    background: #1e293b;
}

@media (max-width: 768px) {
    .admin-sidebar {
        width: 72px;
        flex-basis: 72px;
        padding: 1rem 0.65rem;
    }

    .admin-sidebar__brand {
        padding: 0;
        justify-content: center;
    }

    .admin-sidebar__brand div,
    .admin-sidebar__label,
    .admin-sidebar__link span {
        display: none;
    }

    .admin-sidebar__submenu,
    .admin-sidebar__chevron {
        display: none;
    }

    .admin-sidebar__link {
        justify-content: center;
        font-size: 1.1rem;
    }
}
</style>
