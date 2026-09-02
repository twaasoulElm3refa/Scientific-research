<template>
    <section class="documents-list">
        <header class="documents-list__header">
            <div>
                <span>Documents</span>
                <h2>All Documents</h2>
                <p>Search and review documents stored in Google Drive.</p>
            </div>
            <RouterLink :to="{ name: 'admin.documents.create' }" class="documents-list__add">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                Add Document
            </RouterLink>
        </header>

        <form class="documents-filters" @submit.prevent="loadDocuments(1)">
            <div class="documents-filters__search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input v-model.trim="filters.search" type="search" placeholder="Search documents and rights metadata..." />
            </div>
            <select v-model="filters.source_id" aria-label="Filter by source" @change="loadDocuments(1)">
                <option value="">All sources</option>
                <option v-for="item in lookups.sources" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.category_id" aria-label="Filter by category" @change="loadDocuments(1)">
                <option value="">All categories</option>
                <option v-for="item in lookups.categories" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.document_type_id" aria-label="Filter by document type" @change="loadDocuments(1)">
                <option value="">All types</option>
                <option v-for="item in lookups.documentTypes" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.language_id" aria-label="Filter by language" @change="loadDocuments(1)">
                <option value="">All languages</option>
                <option v-for="item in lookups.languages" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.license_type_id" aria-label="Filter by license type" @change="loadDocuments(1)">
                <option value="">All licenses</option>
                <option v-for="item in lookups.licenseTypes" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.rights_status_id" aria-label="Filter by rights status" @change="loadDocuments(1)">
                <option value="">All rights statuses</option>
                <option v-for="item in lookups.rightsStatuses" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <select v-model="filters.sorting" aria-label="Sort documents" @change="loadDocuments(1)">
                <option value="created_at:desc">Newest first</option>
                <option value="created_at:asc">Oldest first</option>
                <option value="title:asc">File name A–Z</option>
                <option value="publication_date:desc">Latest publication</option>
            </select>
            <button type="submit">Search</button>
        </form>

        <div v-if="errorMessage" class="documents-list__error" role="alert">{{ errorMessage }}</div>

        <div class="documents-table-wrap">
            <table class="documents-table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Source</th>
                        <th>Document Type</th>
                        <th>Author</th>
                        <th>License Type</th>
                        <th>Rights Status</th>
                        <th>DOI</th>
                        <th>URL</th>
                        <th>Upload Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="9" class="documents-table__state">
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            Loading documents...
                        </td>
                    </tr>
                    <tr v-else-if="documents.length === 0">
                        <td colspan="9" class="documents-table__state">No documents found.</td>
                    </tr>
                    <tr v-for="document in documents" v-else :key="document.id">
                        <td>
                            <strong>{{ document.file_name }}</strong>
                            <a v-if="document.drive.web_view_link" :href="document.drive.web_view_link" target="_blank" rel="noopener noreferrer" class="documents-table__drive-link">Open file</a>
                        </td>
                        <td>{{ document.source?.name || "—" }}</td>
                        <td>{{ document.document_type?.name || "—" }}</td>
                        <td><span class="documents-table__authors">{{ authorNames(document) }}</span></td>
                        <td>{{ document.license_type?.name || "—" }}</td>
                        <td>{{ document.rights_status?.name || "—" }}</td>
                        <td>{{ document.doi || "—" }}</td>
                        <td>
                            <a v-if="document.url" :href="document.url" target="_blank" rel="noopener noreferrer" class="documents-table__url">{{ document.url }}</a>
                            <span v-else>—</span>
                        </td>
                        <td>{{ formatDate(document.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer v-if="meta.total" class="documents-pagination">
            <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} documents</span>
            <div>
                <button type="button" :disabled="meta.current_page <= 1 || loading" @click="loadDocuments(meta.current_page - 1)">Previous</button>
                <button type="button" :disabled="meta.current_page >= meta.last_page || loading" @click="loadDocuments(meta.current_page + 1)">Next</button>
            </div>
        </footer>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import api from "../../../services/api";

const documents = ref([]);
const loading = ref(true);
const errorMessage = ref("");
const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
const filters = reactive({
    search: "",
    source_id: "",
    category_id: "",
    document_type_id: "",
    language_id: "",
    license_type_id: "",
    rights_status_id: "",
    sorting: "created_at:desc",
});
const lookups = reactive({ sources: [], categories: [], documentTypes: [], languages: [], licenseTypes: [], rightsStatuses: [] });

const fetchLookup = async (type) => {
    const response = await api.get(`/admin/documents/lookups/${type}`, { params: { per_page: 100 } });
    return response.data.data;
};

const loadDocuments = async (page = 1) => {
    loading.value = true;
    errorMessage.value = "";
    const [sort, direction] = filters.sorting.split(":");

    try {
        const response = await api.get("/admin/documents", {
            params: {
                page,
                per_page: 15,
                search: filters.search || undefined,
                source_id: filters.source_id || undefined,
                category_id: filters.category_id || undefined,
                document_type_id: filters.document_type_id || undefined,
                language_id: filters.language_id || undefined,
                license_type_id: filters.license_type_id || undefined,
                rights_status_id: filters.rights_status_id || undefined,
                sort,
                direction,
            },
        });
        documents.value = response.data.data;
        Object.assign(meta, response.data.meta);
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Documents could not be loaded.";
    } finally {
        loading.value = false;
    }
};

const authorNames = (document) => {
    const names = document.authors.map((author) => author.name);
    if (names.length <= 3) return names.join(", ") || "—";
    return `${names.slice(0, 3).join(", ")} +${names.length - 3}`;
};

const formatDate = (value) => value
    ? new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(new Date(value))
    : "—";

onMounted(async () => {
    try {
        const [sources, categories, documentTypes, languages, licenseTypes, rightsStatuses] = await Promise.all([
            fetchLookup("sources"),
            fetchLookup("categories"),
            fetchLookup("document-types"),
            fetchLookup("languages"),
            fetchLookup("license-types"),
            fetchLookup("rights-statuses"),
        ]);
        Object.assign(lookups, { sources, categories, documentTypes, languages, licenseTypes, rightsStatuses });
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Filter data could not be loaded.";
    }
    await loadDocuments();
});
</script>

<style scoped>
.documents-list { color: #172033; }
.documents-list__header { margin-bottom: 1.3rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.documents-list__header > div > span { color: #2563eb; font-size: .72rem; font-weight: 750; letter-spacing: .11em; text-transform: uppercase; }
.documents-list__header h2 { margin: .2rem 0; font-size: 1.75rem; font-weight: 780; }
.documents-list__header p { margin: 0; color: #64748b; font-size: .86rem; }
.documents-list__add { min-height: 42px; padding: .62rem .9rem; border-radius: 9px; color: #fff; background: #2563eb; display: inline-flex; align-items: center; gap: .45rem; font-size: .85rem; font-weight: 700; text-decoration: none; }
.documents-filters { margin-bottom: 1rem; padding: .9rem; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; display: grid; grid-template-columns: repeat(4, minmax(145px, 1fr)); gap: .6rem; }
.documents-filters input, .documents-filters select { width: 100%; min-height: 40px; padding: .5rem .65rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: 0; color: #334155; background: #fff; font-size: .78rem; }
.documents-filters__search { position: relative; grid-column: span 2; }
.documents-filters__search i { position: absolute; top: 50%; left: .7rem; color: #94a3b8; transform: translateY(-50%); }
.documents-filters__search input { padding-left: 2rem; }
.documents-filters button { padding: .5rem .8rem; border: 0; border-radius: 8px; color: #fff; background: #2563eb; font-size: .8rem; font-weight: 700; }
.documents-list__error { margin-bottom: 1rem; padding: .75rem; border: 1px solid #fecaca; border-radius: 8px; color: #b91c1c; background: #fef2f2; font-size: .82rem; }
.documents-table-wrap { border: 1px solid #e2e8f0; border-radius: 12px; overflow-x: auto; background: #fff; }
.documents-table { width: 100%; min-width: 1350px; border-collapse: collapse; }
.documents-table th { padding: .75rem; border-bottom: 1px solid #e2e8f0; color: #64748b; background: #f8fafc; font-size: .7rem; letter-spacing: .04em; text-align: left; text-transform: uppercase; }
.documents-table td { max-width: 190px; padding: .8rem .75rem; border-bottom: 1px solid #eef2f7; color: #334155; font-size: .78rem; vertical-align: top; }
.documents-table tr:last-child td { border-bottom: 0; }
.documents-table td strong, .documents-table td small { display: block; }
.documents-table td strong { color: #172033; }
.documents-table__authors { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.documents-table__drive-link { margin-top: .25rem; display: inline-block; color: #2563eb; font-size: .72rem; text-decoration: none; }
.documents-table__url { max-width: 190px; display: block; overflow: hidden; color: #2563eb; text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
.documents-table__state { height: 150px; color: #64748b !important; text-align: center !important; vertical-align: middle !important; }
.documents-pagination { padding: 1rem .2rem; display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: .78rem; }
.documents-pagination div { display: flex; gap: .45rem; }
.documents-pagination button { padding: .45rem .7rem; border: 1px solid #cbd5e1; border-radius: 7px; color: #334155; background: #fff; font-size: .76rem; }
.documents-pagination button:disabled { color: #94a3b8; background: #f1f5f9; }
@media (max-width: 1100px) { .documents-filters { grid-template-columns: repeat(3, 1fr); } .documents-filters__search { grid-column: 1 / -1; } }
@media (max-width: 650px) { .documents-list__header { flex-direction: column; } .documents-filters { grid-template-columns: 1fr; } .documents-filters__search { grid-column: auto; } .documents-pagination { align-items: flex-start; flex-direction: column; gap: .65rem; } }
</style>
