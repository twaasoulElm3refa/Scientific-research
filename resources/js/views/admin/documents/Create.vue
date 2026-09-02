<template>
    <section class="document-page">
        <header class="document-page__header">
            <div>
                <span>Documents</span>
                <h2>Add Document</h2>
                <p>Upload a document to Google Drive and save its catalog information.</p>
            </div>
            <RouterLink :to="{ name: 'admin.documents.index' }" class="document-page__secondary">
                <i class="bi bi-list-ul" aria-hidden="true"></i>
                All Documents
            </RouterLink>
        </header>

        <div v-if="successMessage" class="document-alert document-alert--success" role="status">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="document-alert document-alert--error" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            {{ errorMessage }}
        </div>

        <form class="document-form" enctype="multipart/form-data" @submit.prevent="submitDocument">
            <div class="document-card">
                <div class="document-card__title">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <div>
                        <h3>Document file</h3>
                        <p>PDF, DOCX, PPTX, or TXT. No application file-size limit applies.</p>
                    </div>
                </div>

                <label class="document-upload" :class="{ 'is-invalid': fieldError('document_file') }">
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".pdf,.docx,.pptx,.txt"
                        :disabled="submitting"
                        @change="selectFile"
                    />
                    <i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i>
                    <strong>{{ selectedFile?.name || "Choose a document" }}</strong>
                    <span>{{ selectedFile ? formatBytes(selectedFile.size) : "Click to browse" }}</span>
                </label>
                <small v-if="fieldError('document_file')" class="field-error">{{ fieldError("document_file") }}</small>
            </div>

            <div class="document-card">
                <div class="document-card__title">
                    <i class="bi bi-journal-text" aria-hidden="true"></i>
                    <div>
                        <h3>Catalog details</h3>
                        <p>Required publishing and classification metadata.</p>
                    </div>
                </div>

                <div class="document-grid">
                    <div class="form-field">
                        <label for="file-name">File Name (Automatic)</label>
                        <input id="file-name" :value="selectedFile?.name || ''" type="text" placeholder="Generated from the selected file" readonly />
                    </div>

                    <div class="form-field">
                        <label for="document-title">Document Title <b>*</b></label>
                        <input id="document-title" v-model.trim="form.title" type="text" maxlength="500" />
                        <small v-if="fieldError('title')" class="field-error">{{ fieldError("title") }}</small>
                    </div>

                    <SearchableCreatableSelect
                        v-model="form.source_id"
                        label="Publisher / Source"
                        type="sources"
                        placeholder="Search publishers..."
                        :error="fieldError('source_id')"
                        @selected="selectedSource = $event"
                    />
                    <SearchableCreatableSelect
                        v-if="showsJournalFields"
                        v-model="form.magazine_id"
                        label="Journal / Magazine"
                        type="magazines"
                        placeholder="Search journals..."
                        parent-param="source_id"
                        :parent-id="form.source_id"
                        :parent-label="selectedSource?.name || ''"
                        dependency-message="Select a source first."
                        :error="fieldError('magazine_id')"
                        @selected="selectedMagazine = $event"
                    />
                    <SearchableCreatableSelect
                        v-model="form.document_type_id"
                        label="Document Type"
                        type="document-types"
                        placeholder="Search document types..."
                        required
                        :error="fieldError('document_type_id')"
                        @selected="selectedDocumentType = $event"
                    />
                    <SearchableCreatableSelect
                        v-model="form.language_id"
                        label="Language"
                        type="languages"
                        placeholder="Search languages..."
                        required
                        :error="fieldError('language_id')"
                    />

                    <div class="form-field">
                        <label for="doi">DOI</label>
                        <input id="doi" v-model.trim="form.doi" type="text" placeholder="10.xxxx/identifier" maxlength="255" />
                        <small v-if="fieldError('doi')" class="field-error">{{ fieldError("doi") }}</small>
                    </div>

                    <div v-if="isBookType" class="form-field">
                        <label for="isbn">ISBN</label>
                        <input id="isbn" v-model.trim="form.isbn" type="text" maxlength="100" />
                        <small v-if="fieldError('isbn')" class="field-error">{{ fieldError("isbn") }}</small>
                    </div>

                    <div v-if="showsJournalFields" class="form-field">
                        <label for="issn">ISSN</label>
                        <input id="issn" v-model.trim="form.issn" type="text" maxlength="100" />
                        <small v-if="fieldError('issn')" class="field-error">{{ fieldError("issn") }}</small>
                    </div>

                    <div class="form-field">
                        <label for="document-url">Original Source URL</label>
                        <input id="document-url" v-model.trim="form.url" type="url" placeholder="https://example.com/document" maxlength="2048" />
                        <small v-if="fieldError('url')" class="field-error">{{ fieldError("url") }}</small>
                    </div>

                    <SearchableCreatableSelect
                        v-model="form.license_type_id"
                        label="License Type"
                        type="license-types"
                        placeholder="Search licenses..."
                        :allow-create="false"
                        :error="fieldError('license_type_id')"
                    />
                    <SearchableCreatableSelect
                        v-model="form.rights_status_id"
                        label="Rights Status"
                        type="rights-statuses"
                        placeholder="Search rights statuses..."
                        :allow-create="false"
                        required
                        :error="fieldError('rights_status_id')"
                    />

                    <div class="form-field">
                        <label for="publish-year">Publish Year</label>
                        <input id="publish-year" v-model="form.publish_year" type="number" min="1000" :max="nextYear" />
                        <small v-if="fieldError('publish_year')" class="field-error">{{ fieldError("publish_year") }}</small>
                    </div>

                    <div class="form-field">
                        <label for="publish-date">Publish Month</label>
                        <input id="publish-date" v-model="form.publish_date" type="month" />
                        <small v-if="fieldError('publish_date')" class="field-error">{{ fieldError("publish_date") }}</small>
                    </div>

                    <div class="form-field">
                        <label for="pages-number">Document Pages Number</label>
                        <input id="pages-number" v-model="form.pages_number" type="number" min="1" />
                        <small v-if="fieldError('pages_number')" class="field-error">{{ fieldError("pages_number") }}</small>
                    </div>

                    <SearchableCreatableSelect
                        v-model="form.country_id"
                        label="Country"
                        type="countries"
                        placeholder="Search countries..."
                        :error="fieldError('country_id')"
                    />
                </div>
            </div>

            <div class="document-card">
                <div class="document-card__title">
                    <i class="bi bi-diagram-3" aria-hidden="true"></i>
                    <div>
                        <h3>Classification</h3>
                        <p>Subcategories and specializations are validated against their parents.</p>
                    </div>
                </div>

                <div class="document-grid">
                    <SearchableCreatableSelect
                        v-model="form.category_id"
                        label="Main Category"
                        type="categories"
                        placeholder="ابحث عن التصنيف..."
                        required
                        :error="fieldError('category_id')"
                        @selected="selectedCategory = $event"
                    />
                    <SearchableCreatableSelect
                        v-model="form.subcategory_id"
                        label="Sub Category"
                        type="subcategories"
                        placeholder="ابحث عن التصنيف الفرعي..."
                        parent-param="category_id"
                        :parent-id="form.category_id"
                        :parent-label="selectedCategory?.name || ''"
                        dependency-message="Select a main category first."
                        :error="fieldError('subcategory_id')"
                        @selected="selectedSubcategory = $event"
                    />
                    <SearchableCreatableSelect
                        v-model="form.specialization_id"
                        label="Specialization"
                        type="specializations"
                        placeholder="ابحث عن التخصص..."
                        parent-param="subcategory_id"
                        :parent-id="form.subcategory_id"
                        :parent-label="selectedSubcategory?.name || ''"
                        dependency-message="Select a subcategory first."
                        :error="fieldError('specialization_id')"
                        @selected="selectedSpecialization = $event"
                    />
                </div>
            </div>

            <div class="document-card">
                <div class="document-card__title">
                    <i class="bi bi-people" aria-hidden="true"></i>
                    <div>
                        <h3>Authors and contributions</h3>
                        <p>Search the database rather than loading every person at once.</p>
                    </div>
                </div>

                <div class="people-grid">
                    <div class="people-picker">
                        <SearchableCreatableSelect
                            v-model="selectedAuthorIds"
                            label="Authors"
                            type="authors"
                            placeholder="Search authors..."
                            multiple
                            required
                            :error="fieldError('author_ids')"
                            @selected="selectedAuthors = $event"
                        />
                    </div>

                    <div class="people-picker">
                        <SearchableCreatableSelect
                            v-model="selectedContributorIds"
                            label="Contributors"
                            type="contributors"
                            placeholder="Search contributors..."
                            multiple
                            @selected="updateSelectedContributors"
                        />
                        <div class="contributor-selected" v-for="(contributor, index) in selectedContributors" :key="contributor.id">
                            <strong>{{ contributor.name }}</strong>
                            <input v-model.trim="contributor.role" type="text" maxlength="100" placeholder="Contribution role (optional)" />
                            <button type="button" :aria-label="`Remove ${contributor.name}`" @click="removeContributor(contributor.id)">×</button>
                            <small v-if="fieldError(`contributors.${index}.role`)" class="field-error">{{ fieldError(`contributors.${index}.role`) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="document-form__actions">
                <RouterLink :to="{ name: 'admin.documents.index' }" class="document-page__secondary">Cancel</RouterLink>
                <button type="submit" class="document-page__primary" :disabled="submitting">
                    <span v-if="submitting" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <i v-else class="bi bi-cloud-check" aria-hidden="true"></i>
                    {{ submitting ? "Uploading and saving..." : "Save Document" }}
                </button>
            </div>
        </form>
    </section>
</template>

<script setup>
import { computed, reactive, ref, watch } from "vue";
import SearchableCreatableSelect from "../../../components/admin/SearchableCreatableSelect.vue";
import api from "../../../services/api";

const newSubmissionId = () => {
    if (crypto.randomUUID) {
        return crypto.randomUUID();
    }

    return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, (character) =>
        (character ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (character / 4)))).toString(16),
    );
};

const defaultForm = () => ({
    submission_id: newSubmissionId(),
    title: "",
    source_id: "",
    magazine_id: "",
    document_type_id: "",
    doi: "",
    isbn: "",
    issn: "",
    url: "",
    license_type_id: "",
    rights_status_id: "",
    language_id: "",
    publish_year: "",
    publish_date: "",
    pages_number: "",
    category_id: "",
    subcategory_id: "",
    specialization_id: "",
    country_id: "",
});

const form = reactive(defaultForm());
const selectedSource = ref(null);
const selectedMagazine = ref(null);
const selectedDocumentType = ref(null);
const selectedCategory = ref(null);
const selectedSubcategory = ref(null);
const selectedSpecialization = ref(null);
const selectedFile = ref(null);
const fileInput = ref(null);
const selectedAuthorIds = ref([]);
const selectedContributorIds = ref([]);
const selectedAuthors = ref([]);
const selectedContributors = ref([]);
const submitting = ref(false);
const validationErrors = ref({});
const errorMessage = ref("");
const successMessage = ref("");
const nextYear = new Date().getFullYear() + 1;
const typeNameContains = (...terms) => {
    const name = selectedDocumentType.value?.name?.toLocaleLowerCase() || "";
    return terms.some((term) => name.includes(term));
};
const showsJournalFields = computed(() => typeNameContains(
    "research",
    "article",
    "journal",
    "بحث",
    "مقال",
    "مجلة",
));
const isBookType = computed(() => typeNameContains("book", "كتاب"));
watch(() => form.category_id, () => {
    form.subcategory_id = "";
    form.specialization_id = "";
    selectedSubcategory.value = null;
    selectedSpecialization.value = null;
});

watch(() => form.subcategory_id, () => {
    form.specialization_id = "";
    selectedSpecialization.value = null;
});

watch(() => form.source_id, () => {
    form.magazine_id = "";
    selectedMagazine.value = null;
});

watch(selectedDocumentType, () => {
    if (!showsJournalFields.value) {
        form.magazine_id = "";
        form.issn = "";
        selectedMagazine.value = null;
    }

    if (!isBookType.value) form.isbn = "";
});

const selectFile = (event) => {
    selectedFile.value = event.target.files?.[0] || null;
    validationErrors.value.document_file = [];
};

const updateSelectedContributors = (contributors) => {
    selectedContributors.value = contributors.map((contributor) => ({
        ...contributor,
        role: selectedContributors.value.find((selected) => selected.id === contributor.id)?.role || "",
    }));
};
const removeContributor = (id) => {
    selectedContributorIds.value = selectedContributorIds.value.filter((selectedId) => selectedId !== id);
    selectedContributors.value = selectedContributors.value.filter((item) => item.id !== id);
};
const fieldError = (field) => validationErrors.value[field]?.[0] || "";

const formatBytes = (bytes) => {
    if (!bytes) return "0 B";
    const units = ["B", "KB", "MB", "GB"];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    return `${(bytes / (1024 ** index)).toFixed(index ? 1 : 0)} ${units[index]}`;
};

const resetForm = () => {
    Object.assign(form, defaultForm());
    selectedFile.value = null;
    selectedAuthorIds.value = [];
    selectedContributorIds.value = [];
    selectedAuthors.value = [];
    selectedContributors.value = [];
    selectedSource.value = null;
    selectedMagazine.value = null;
    selectedDocumentType.value = null;
    selectedCategory.value = null;
    selectedSubcategory.value = null;
    selectedSpecialization.value = null;
    validationErrors.value = {};
    if (fileInput.value) fileInput.value.value = "";
};

const submitDocument = async () => {
    submitting.value = true;
    errorMessage.value = "";
    successMessage.value = "";
    validationErrors.value = {};
    const payload = new FormData();

    Object.entries(form).forEach(([key, value]) => {
        if (value !== "" && value !== null) payload.append(key, value);
    });
    if (selectedFile.value) payload.append("document_file", selectedFile.value);
    selectedAuthors.value.forEach((author, index) => payload.append(`author_ids[${index}]`, author.id));
    selectedContributors.value.forEach((contributor, index) => {
        payload.append(`contributors[${index}][id]`, contributor.id);
        if (contributor.role) payload.append(`contributors[${index}][role]`, contributor.role);
    });

    try {
        const response = await api.post("/admin/documents", payload, {
            headers: { "Content-Type": "multipart/form-data" },
        });
        successMessage.value = response.data.message;
        resetForm();
        window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (error) {
        if (error.response?.status === 422) validationErrors.value = error.response.data.errors || {};
        errorMessage.value = error.response?.data?.message || "The document could not be saved.";
        window.scrollTo({ top: 0, behavior: "smooth" });
    } finally {
        submitting.value = false;
    }
};
</script>

<style scoped>
.document-page { max-width: 1180px; margin: 0 auto; color: #172033; }
.document-page__header { margin-bottom: 1.4rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.document-page__header span { color: #2563eb; font-size: .72rem; font-weight: 750; letter-spacing: .11em; text-transform: uppercase; }
.document-page__header h2 { margin: .2rem 0; font-size: 1.75rem; font-weight: 780; }
.document-page__header p, .document-card__title p { margin: 0; color: #64748b; font-size: .86rem; }
.document-page__secondary, .document-page__primary { min-height: 42px; padding: .62rem .9rem; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; gap: .45rem; font-size: .85rem; font-weight: 700; text-decoration: none; }
.document-page__secondary { border: 1px solid #cbd5e1; color: #334155; background: #fff; }
.document-page__primary { border: 0; color: #fff; background: #2563eb; }
.document-page__primary:disabled { opacity: .65; cursor: wait; }
.document-alert { margin-bottom: 1rem; padding: .8rem 1rem; border-radius: 10px; display: flex; gap: .55rem; font-size: .86rem; }
.document-alert--success { border: 1px solid #bbf7d0; color: #166534; background: #f0fdf4; }
.document-alert--error { border: 1px solid #fecaca; color: #b91c1c; background: #fef2f2; }
.document-form { display: grid; gap: 1rem; }
.document-card { padding: 1.35rem; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 8px 25px rgba(30, 41, 59, .04); }
.document-card__title { margin-bottom: 1.15rem; display: flex; align-items: center; gap: .75rem; }
.document-card__title > i { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; color: #1d4ed8; background: #dbeafe; }
.document-card__title h3 { margin: 0 0 .15rem; font-size: 1rem; font-weight: 750; }
.document-upload { min-height: 150px; padding: 1.25rem; border: 2px dashed #cbd5e1; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; background: #f8fafc; }
.document-upload.is-invalid { border-color: #dc2626; }
.document-upload input { width: 1px; height: 1px; opacity: 0; position: absolute; }
.document-upload > i { margin-bottom: .45rem; color: #2563eb; font-size: 1.65rem; }
.document-upload strong { font-size: .9rem; }
.document-upload span { color: #64748b; font-size: .76rem; }
.document-grid, .people-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.form-field--wide { grid-column: 1 / -1; }
.form-field label, .people-picker > label { margin-bottom: .38rem; display: block; color: #334155; font-size: .8rem; font-weight: 650; }
.form-field b, .people-picker b { color: #dc2626; }
.form-field input, .form-field select, .people-picker > input, .contributor-selected input { width: 100%; min-height: 43px; padding: .58rem .72rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: 0; color: #172033; background: #fff; font-size: .86rem; }
.form-field input:focus, .form-field select:focus, .people-picker > input:focus, .contributor-selected input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .1); }
.form-field select:disabled { color: #94a3b8; background: #f1f5f9; }
.field-error { margin-top: .3rem; display: block; color: #dc2626; font-size: .73rem; }
.people-results { min-height: 50px; max-height: 170px; margin-top: .45rem; padding: .4rem; border: 1px solid #e2e8f0; border-radius: 8px; overflow-y: auto; display: flex; flex-direction: column; gap: .2rem; }
.people-results button { padding: .45rem .55rem; border: 0; border-radius: 6px; color: #334155; background: transparent; display: flex; gap: .4rem; text-align: left; font-size: .8rem; }
.people-results button:hover:not(:disabled) { background: #eff6ff; color: #1d4ed8; }
.people-results button:disabled { color: #94a3b8; }
.people-results > span { padding: .7rem; color: #94a3b8; font-size: .78rem; text-align: center; }
.people-selected { margin-top: .55rem; display: flex; flex-wrap: wrap; gap: .35rem; }
.people-selected span { padding: .36rem .5rem; border-radius: 999px; color: #1e40af; background: #dbeafe; font-size: .76rem; }
.people-selected button, .contributor-selected > button { border: 0; color: inherit; background: transparent; font-weight: 800; }
.contributor-selected { margin-top: .55rem; padding: .55rem; border: 1px solid #e2e8f0; border-radius: 8px; display: grid; grid-template-columns: 1fr 1.4fr auto; align-items: center; gap: .45rem; }
.contributor-selected strong { font-size: .78rem; }
.contributor-selected input { min-height: 36px; }
.document-form__actions { padding: .35rem 0 1rem; display: flex; justify-content: flex-end; gap: .65rem; }
@media (max-width: 760px) { .document-grid, .people-grid { grid-template-columns: 1fr; } .form-field--wide { grid-column: auto; } .document-page__header { flex-direction: column; } }
</style>
