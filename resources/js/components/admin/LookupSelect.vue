<template>
    <div class="lookup-field">
        <label>
            {{ label }}
            <b v-if="required">*</b>
        </label>
        <select :value="modelValue" :disabled="disabled" @change="$emit('update:modelValue', $event.target.value)">
            <option value="">Select {{ label }}</option>
            <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
        </select>
        <small v-if="error">{{ error }}</small>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: [String, Number], default: "" },
    label: { type: String, required: true },
    items: { type: Array, default: () => [] },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: "" },
});

defineEmits(["update:modelValue"]);
</script>

<style scoped>
.lookup-field label {
    margin-bottom: 0.38rem;
    display: block;
    color: #334155;
    font-size: 0.8rem;
    font-weight: 650;
}

.lookup-field b {
    color: #dc2626;
}

.lookup-field select {
    width: 100%;
    min-height: 43px;
    padding: 0.58rem 0.72rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: 0;
    color: #172033;
    background: #ffffff;
    font-size: 0.86rem;
}

.lookup-field select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.lookup-field select:disabled {
    color: #94a3b8;
    background: #f1f5f9;
}

.lookup-field small {
    margin-top: 0.3rem;
    display: block;
    color: #dc2626;
    font-size: 0.73rem;
}
</style>
