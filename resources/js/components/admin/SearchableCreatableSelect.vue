<template>
    <div ref="root" class="form-field searchable-select" :class="{ 'is-disabled': isDisabled }">
        <label :for="inputId">
            {{ label }} <b v-if="required">*</b>
        </label>

        <div v-if="multiple && selectedItems.length" class="searchable-select__selected">
            <span v-for="item in selectedItems" :key="item.id">
                {{ item.name }}
                <button type="button" :aria-label="`Remove ${item.name}`" :disabled="isDisabled" @click="removeItem(item.id)">×</button>
            </span>
        </div>

        <div class="searchable-select__control" :class="{ 'is-open': open, 'is-invalid': displayedError }">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                :id="inputId"
                ref="input"
                type="search"
                role="combobox"
                autocomplete="off"
                :aria-expanded="open"
                :aria-controls="listboxId"
                :aria-activedescendant="activeDescendant"
                :disabled="isDisabled"
                :placeholder="effectivePlaceholder"
                :value="inputValue"
                @focus="openDropdown"
                @input="updateQuery"
                @keydown="handleKeydown"
            />
            <button
                v-if="hasSelection && !isDisabled"
                type="button"
                class="searchable-select__clear"
                :aria-label="`Clear ${label}`"
                @mousedown.prevent
                @click="clearSelection"
            >
                ×
            </button>
            <i v-else class="bi bi-chevron-down searchable-select__chevron" aria-hidden="true"></i>
        </div>

        <div v-if="open" :id="listboxId" class="searchable-select__menu" role="listbox" :aria-multiselectable="multiple || undefined">
            <div v-if="loading" class="searchable-select__status">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                Searching...
            </div>

            <template v-else>
                <button
                    v-for="(item, index) in results"
                    :id="optionId(index)"
                    :key="item.id"
                    type="button"
                    role="option"
                    class="searchable-select__option"
                    :class="{ 'is-active': activeIndex === index, 'is-selected': isSelected(item.id) }"
                    :aria-selected="isSelected(item.id)"
                    @mouseenter="activeIndex = index"
                    @mousedown.prevent
                    @click="selectItem(item)"
                >
                    <span>{{ item.name }}</span>
                    <i v-if="isSelected(item.id)" class="bi bi-check2" aria-hidden="true"></i>
                </button>

                <div v-if="results.length === 0" class="searchable-select__status">لا توجد نتائج</div>

                <button
                    v-if="canCreate"
                    type="button"
                    class="searchable-select__create"
                    :class="{ 'is-active': activeIndex === results.length }"
                    :disabled="creating"
                    @mouseenter="activeIndex = results.length"
                    @mousedown.prevent
                    @click="createItem"
                >
                    <span v-if="creating" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    <i v-else class="bi bi-plus-circle" aria-hidden="true"></i>
                    <span>
                        إضافة &quot;{{ cleanQuery }}&quot;
                        <template v-if="parentLabel"> إلى &quot;{{ parentLabel }}&quot;</template>
                    </span>
                </button>
            </template>
        </div>

        <small v-if="displayedError" class="field-error">{{ displayedError }}</small>
        <small v-else-if="parentParam && !parentId" class="searchable-select__hint">{{ dependencyMessage }}</small>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import api from "../../services/api";

const props = defineProps({
    modelValue: { type: [String, Number, Array], default: "" },
    label: { type: String, required: true },
    type: { type: String, required: true },
    placeholder: { type: String, default: "Search..." },
    parentId: { type: [String, Number], default: "" },
    parentParam: { type: String, default: "" },
    parentLabel: { type: String, default: "" },
    dependencyMessage: { type: String, default: "Select the parent value first." },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    allowCreate: { type: Boolean, default: true },
    multiple: { type: Boolean, default: false },
    error: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue", "selected"]);
const root = ref(null);
const input = ref(null);
const open = ref(false);
const query = ref("");
const results = ref([]);
const selectedItem = ref(null);
const selectedItems = ref([]);
const loading = ref(false);
const creating = ref(false);
const localError = ref("");
const activeIndex = ref(-1);
const inputId = `lookup-${Math.random().toString(36).slice(2, 10)}`;
const listboxId = `${inputId}-listbox`;
let searchTimer;
let requestNumber = 0;

const isDisabled = computed(() => props.disabled || Boolean(props.parentParam && !props.parentId));
const cleanQuery = computed(() => query.value.trim().replace(/\s+/g, " "));
const inputValue = computed(() => open.value || props.multiple ? query.value : (selectedItem.value?.name || query.value));
const effectivePlaceholder = computed(() => isDisabled.value ? props.dependencyMessage : props.placeholder);
const displayedError = computed(() => localError.value || props.error);
const hasSelection = computed(() => props.multiple ? selectedItems.value.length > 0 : Boolean(props.modelValue));
const hasEquivalentResult = computed(() => {
    const wanted = cleanQuery.value.toLocaleLowerCase();
    return results.value.some((item) => item.name.trim().replace(/\s+/g, " ").toLocaleLowerCase() === wanted);
});
const canCreate = computed(() => props.allowCreate && cleanQuery.value && !hasEquivalentResult.value && !isDisabled.value);
const activeDescendant = computed(() => {
    if (!open.value || activeIndex.value < 0 || activeIndex.value >= results.value.length) return undefined;
    return optionId(activeIndex.value);
});

const optionId = (index) => `${inputId}-option-${index}`;
const isSelected = (id) => props.multiple
    ? selectedItems.value.some((item) => Number(item.id) === Number(id))
    : Number(props.modelValue) === Number(id);

const requestParams = () => {
    const params = { search: cleanQuery.value, per_page: 20 };
    if (props.parentParam && props.parentId) params[props.parentParam] = props.parentId;
    return params;
};

const search = async () => {
    if (isDisabled.value) {
        results.value = [];
        return;
    }

    const currentRequest = ++requestNumber;
    loading.value = true;
    localError.value = "";

    try {
        const response = await api.get(`/admin/documents/lookups/${props.type}`, { params: requestParams() });
        if (currentRequest === requestNumber) {
            results.value = response.data.data || [];
            activeIndex.value = results.value.length ? 0 : (canCreate.value ? 0 : -1);
        }
    } catch (error) {
        if (currentRequest === requestNumber) {
            results.value = [];
            localError.value = error.response?.data?.message || "تعذر تحميل الخيارات.";
        }
    } finally {
        if (currentRequest === requestNumber) loading.value = false;
    }
};

const queueSearch = () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(search, 300);
};

const openDropdown = () => {
    if (isDisabled.value) return;
    open.value = true;
    query.value = "";
    localError.value = "";
    search();
};

const closeDropdown = () => {
    open.value = false;
    query.value = props.multiple ? "" : (selectedItem.value?.name || "");
    activeIndex.value = -1;
};

const updateQuery = (event) => {
    query.value = event.target.value;
    open.value = true;
    localError.value = "";
    queueSearch();
};

const selectItem = (item) => {
    if (props.multiple) {
        if (isSelected(item.id)) return;

        selectedItems.value = [...selectedItems.value, item];
        query.value = "";
        emit("update:modelValue", selectedItems.value.map((selected) => selected.id));
        emit("selected", [...selectedItems.value]);
        search();
        input.value?.focus();
        return;
    }

    selectedItem.value = item;
    query.value = item.name;
    emit("update:modelValue", item.id);
    emit("selected", item);
    closeDropdown();
};

const clearSelection = async () => {
    selectedItem.value = null;
    selectedItems.value = [];
    query.value = "";
    emit("update:modelValue", props.multiple ? [] : "");
    emit("selected", props.multiple ? [] : null);
    open.value = true;
    await nextTick();
    input.value?.focus();
    search();
};

const removeItem = (id) => {
    if (!props.multiple) return;

    selectedItems.value = selectedItems.value.filter((item) => Number(item.id) !== Number(id));
    emit("update:modelValue", selectedItems.value.map((item) => item.id));
    emit("selected", [...selectedItems.value]);
};

const createItem = async () => {
    if (!canCreate.value || creating.value) return;

    creating.value = true;
    localError.value = "";
    const payload = { name: cleanQuery.value };
    if (props.parentParam) payload[props.parentParam] = props.parentId;

    try {
        const response = await api.post(`/admin/documents/lookups/${props.type}`, payload);
        const item = response.data.data;
        results.value = [item, ...results.value.filter((result) => result.id !== item.id)];
        selectItem(item);
    } catch (error) {
        const errors = error.response?.data?.errors || {};
        localError.value = errors.name?.[0]
            || errors[props.parentParam]?.[0]
            || error.response?.data?.message
            || "تعذر إضافة القيمة.";
    } finally {
        creating.value = false;
    }
};

const handleKeydown = (event) => {
    const finalIndex = results.value.length + (canCreate.value ? 1 : 0) - 1;

    if (event.key === "ArrowDown") {
        event.preventDefault();
        if (!open.value) return openDropdown();
        activeIndex.value = Math.min(activeIndex.value + 1, finalIndex);
    } else if (event.key === "ArrowUp") {
        event.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
    } else if (event.key === "Enter" && open.value && activeIndex.value >= 0) {
        event.preventDefault();
        activeIndex.value < results.value.length
            ? selectItem(results.value[activeIndex.value])
            : createItem();
    } else if (event.key === "Escape") {
        closeDropdown();
    }
};

const closeOnOutsideClick = (event) => {
    if (root.value && !root.value.contains(event.target)) closeDropdown();
};

watch(() => props.modelValue, (value) => {
    if (props.multiple) {
        const ids = Array.isArray(value) ? value.map(Number) : [];
        selectedItems.value = selectedItems.value.filter((item) => ids.includes(Number(item.id)));
        return;
    }

    if (!value) {
        selectedItem.value = null;
        query.value = "";
        emit("selected", null);
    }
});

watch(() => props.parentId, (value, oldValue) => {
    if (value === oldValue) return;
    const shouldClear = props.multiple
        ? Array.isArray(props.modelValue) && props.modelValue.length > 0
        : Boolean(props.modelValue);
    selectedItem.value = null;
    selectedItems.value = [];
    query.value = "";
    results.value = [];
    localError.value = "";
    if (shouldClear) emit("update:modelValue", props.multiple ? [] : "");
    emit("selected", props.multiple ? [] : null);
    if (open.value && value) search();
});

onMounted(() => document.addEventListener("mousedown", closeOnOutsideClick));
onBeforeUnmount(() => {
    document.removeEventListener("mousedown", closeOnOutsideClick);
    window.clearTimeout(searchTimer);
    requestNumber += 1;
});
</script>

<style scoped>
.searchable-select { position: relative; }
.searchable-select__selected { margin-bottom: .4rem; display: flex; flex-wrap: wrap; gap: .35rem; }
.searchable-select__selected span { padding: .34rem .5rem; border-radius: 999px; color: #1e40af; background: #dbeafe; font-size: .76rem; }
.searchable-select__selected button { padding: 0 0 0 .25rem; border: 0; color: inherit; background: transparent; font-weight: 800; }
.searchable-select__control { min-height: 42px; padding: 0 .72rem; border: 1px solid #cbd5e1; border-radius: 9px; display: flex; align-items: center; gap: .55rem; color: #64748b; background: #fff; transition: border-color .15s, box-shadow .15s; }
.searchable-select__control:focus-within, .searchable-select__control.is-open { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
.searchable-select__control.is-invalid { border-color: #ef4444; }
.searchable-select__control input { min-width: 0; width: 100%; padding: .62rem 0; border: 0; outline: 0; color: #172033; background: transparent; font-size: .86rem; }
.searchable-select__clear { width: 24px; height: 24px; padding: 0; border: 0; border-radius: 50%; color: #64748b; background: #f1f5f9; font-size: 1rem; line-height: 1; }
.searchable-select__chevron { font-size: .72rem; }
.searchable-select__menu { position: absolute; z-index: 40; top: calc(100% - 1px); left: 0; right: 0; max-height: 250px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; box-shadow: 0 14px 30px rgba(15, 23, 42, .13); }
.searchable-select__option, .searchable-select__create { width: 100%; min-height: 40px; padding: .62rem .78rem; border: 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: .6rem; text-align: start; color: #334155; background: #fff; font-size: .84rem; }
.searchable-select__option:hover, .searchable-select__option.is-active { background: #eff6ff; }
.searchable-select__option.is-selected { color: #1d4ed8; font-weight: 700; }
.searchable-select__create { justify-content: flex-start; color: #1d4ed8; font-weight: 700; background: #f8fafc; }
.searchable-select__create:hover, .searchable-select__create.is-active { background: #dbeafe; }
.searchable-select__status { min-height: 42px; padding: .7rem .78rem; display: flex; align-items: center; gap: .5rem; color: #64748b; font-size: .82rem; }
.searchable-select__hint { color: #64748b; }
.is-disabled .searchable-select__control { color: #94a3b8; background: #f8fafc; cursor: not-allowed; }
</style>
