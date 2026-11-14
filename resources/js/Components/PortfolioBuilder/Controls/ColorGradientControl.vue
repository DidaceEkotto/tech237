<script setup>
const props = defineProps({
    modelValue: { type: String, default: '#ffffff' },
    label: { type: String, default: 'Couleur' },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="mb-2">
        <label class="form-label small fw-semibold text-secondary mb-1">{{ label }}</label>
        <div class="input-group input-group-sm">
            <input
                type="color"
                class="form-control form-control-color"
                style="max-width: 42px;"
                :value="modelValue && modelValue.startsWith('#') ? modelValue : '#4f46e5'"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <input
                type="text"
                class="form-control"
                :value="modelValue"
                placeholder="#ffffff ou transparent"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>
    </div>
</template>
