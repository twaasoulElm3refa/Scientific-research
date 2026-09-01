<template>
    <section class="drive-settings">
        <header class="drive-settings__heading">
            <div>
                <span class="drive-settings__eyebrow">Settings</span>
                <h2>Google Drive Integration</h2>
                <p>Connect the personal Google Drive account used to store uploaded documents.</p>
            </div>
        </header>

        <div
            v-if="notice.message"
            class="alert"
            :class="notice.type === 'success' ? 'alert-success' : 'alert-danger'"
            role="alert"
        >
            {{ notice.message }}
        </div>

        <div class="drive-card" :aria-busy="loading">
            <div class="drive-card__status">
                <span
                    class="drive-card__icon"
                    :class="connection.connected ? 'drive-card__icon--connected' : ''"
                    aria-hidden="true"
                >
                    <i :class="connection.connected ? 'bi bi-cloud-check-fill' : 'bi bi-cloud-slash-fill'"></i>
                </span>

                <div>
                    <span class="drive-card__label">Connection status</span>
                    <h3>{{ loading ? "Checking connection..." : statusLabel }}</h3>
                    <p v-if="connection.connected">
                        Document uploads are authorized for Google Drive.
                    </p>
                    <p v-else>
                        Connect an account before uploading documents.
                    </p>
                </div>
            </div>

            <dl v-if="connection.connected" class="drive-card__details">
                <div v-if="connection.connected_by">
                    <dt>Connected by</dt>
                    <dd>{{ connection.connected_by.name }} ({{ connection.connected_by.email }})</dd>
                </div>
                <div v-if="connection.updated_at">
                    <dt>Last updated</dt>
                    <dd>{{ formatDate(connection.updated_at) }}</dd>
                </div>
            </dl>

            <div class="drive-card__actions">
                <button
                    v-if="!connection.connected"
                    type="button"
                    class="btn btn-primary"
                    :disabled="loading || submitting"
                    @click="connect"
                >
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    Connect Google Drive
                </button>

                <template v-else>
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        :disabled="loading || submitting"
                        @click="refreshConnection"
                    >
                        Refresh connection
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        :disabled="loading || submitting"
                        @click="disconnect"
                    >
                        Disconnect
                    </button>
                </template>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../../../services/api";

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const submitting = ref(false);
const connection = reactive({
    connected: false,
    connected_at: null,
    updated_at: null,
    expires_at: null,
    connected_by: null,
});
const notice = reactive({ type: "success", message: "" });

const statusLabel = computed(() => (connection.connected ? "Connected" : "Not connected"));

const errorMessage = (error, fallback) => error.response?.data?.message || fallback;

const setConnection = (data) => {
    Object.assign(connection, {
        connected: Boolean(data.connected),
        connected_at: data.connected_at || null,
        updated_at: data.updated_at || null,
        expires_at: data.expires_at || null,
        connected_by: data.connected_by || null,
    });
};

const loadStatus = async () => {
    loading.value = true;

    try {
        const response = await api.get("/admin/google-drive");
        setConnection(response.data);
    } catch (error) {
        notice.type = "error";
        notice.message = errorMessage(error, "The Google Drive connection status could not be loaded.");
    } finally {
        loading.value = false;
    }
};

const connect = async () => {
    submitting.value = true;
    notice.message = "";

    try {
        const response = await api.post("/admin/google-drive/authorize");
        window.location.assign(response.data.authorization_url);
    } catch (error) {
        notice.type = "error";
        notice.message = errorMessage(error, "Google Drive authorization could not be started.");
        submitting.value = false;
    }
};

const refreshConnection = async () => {
    submitting.value = true;
    notice.message = "";

    try {
        const response = await api.post("/admin/google-drive/refresh");
        notice.type = "success";
        notice.message = response.data.message;
        await loadStatus();
    } catch (error) {
        notice.type = "error";
        notice.message = errorMessage(error, "The Google Drive connection could not be refreshed.");
    } finally {
        submitting.value = false;
    }
};

const disconnect = async () => {
    if (!window.confirm("Disconnect Google Drive? Document uploads will stop until it is reconnected.")) {
        return;
    }

    submitting.value = true;
    notice.message = "";

    try {
        const response = await api.delete("/admin/google-drive");
        notice.type = "success";
        notice.message = response.data.message;
        await loadStatus();
    } catch (error) {
        notice.type = "error";
        notice.message = errorMessage(error, "Google Drive could not be disconnected.");
    } finally {
        submitting.value = false;
    }
};

const formatDate = (value) => new Intl.DateTimeFormat(undefined, {
    dateStyle: "medium",
    timeStyle: "short",
}).format(new Date(value));

const applyCallbackNotice = async () => {
    const result = route.query.google_drive;

    if (!result) {
        return;
    }

    const messages = {
        connected: ["success", "Google Drive connected successfully."],
        denied: ["error", "Google Drive access was not granted."],
        failed: ["error", "Google Drive authorization could not be completed. Please try again."],
        "invalid-state": ["error", "The connection request expired. Please start again."],
    };
    const [type, message] = messages[result] || messages.failed;
    notice.type = type;
    notice.message = message;

    const query = { ...route.query };
    delete query.google_drive;
    await router.replace({ query });
};

onMounted(async () => {
    await applyCallbackNotice();
    await loadStatus();
});
</script>

<style scoped>
.drive-settings {
    max-width: 880px;
}

.drive-settings__heading {
    margin-bottom: 1.25rem;
}

.drive-settings__eyebrow,
.drive-card__label {
    color: #2563eb;
    font-size: 0.72rem;
    font-weight: 750;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.drive-settings h2 {
    margin: 0.3rem 0;
    color: #172033;
    font-size: clamp(1.55rem, 3vw, 2rem);
}

.drive-settings__heading p,
.drive-card__status p {
    margin: 0;
    color: #64748b;
}

.drive-card {
    padding: clamp(1.25rem, 4vw, 2rem);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 12px 32px rgba(30, 41, 59, 0.06);
}

.drive-card__status {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.drive-card__icon {
    width: 56px;
    height: 56px;
    border-radius: 15px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    color: #64748b;
    background: #f1f5f9;
    font-size: 1.35rem;
}

.drive-card__icon--connected {
    color: #15803d;
    background: #dcfce7;
}

.drive-card h3 {
    margin: 0.2rem 0 0.25rem;
    color: #172033;
    font-size: 1.25rem;
}

.drive-card__details {
    margin: 1.5rem 0 0;
    padding: 1rem 0;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    display: grid;
    gap: 0.85rem;
}

.drive-card__details div {
    display: grid;
    grid-template-columns: 125px minmax(0, 1fr);
    gap: 0.75rem;
}

.drive-card__details dt {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 650;
}

.drive-card__details dd {
    margin: 0;
    color: #334155;
    overflow-wrap: anywhere;
}

.drive-card__actions {
    margin-top: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

@media (max-width: 560px) {
    .drive-card__status {
        align-items: flex-start;
        flex-direction: column;
    }

    .drive-card__details div {
        grid-template-columns: 1fr;
        gap: 0.2rem;
    }
}
</style>
