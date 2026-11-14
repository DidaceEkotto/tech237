<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '#ffffff' },
    label: { type: String, default: 'Couleur / Dégradé' },
    allowGradient: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);
const gradMode = ref(false);
const c1 = ref('#4f46e5');
const c2 = ref('#06b6d4');
const angle = ref(135);

function applyGradient() {
    emit('update:modelValue', `linear-gradient(${angle.value}deg, ${c1.value} 0%, ${c2.value} 100%)`);
}
</script>

<template>
    <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label small fw-semibold text-secondary mb-0">{{ label }}</label>
            <button
                v-if="allowGradient"
                type="button"
                class="btn btn-link btn-sm p-0 text-decoration-none"
                style="font-size: 11px;"
                @click="gradMode = !gradMode"
            >
                {{ gradMode ? 'Couleur unie' : 'Dégradé' }}
            </button>
        </div>

        <div v-if="!gradMode" class="input-group input-group-sm">
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
                placeholder="#ffffff ou linear-gradient(...)"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <div v-else class="p-2 border rounded-3 bg-light">
            <div class="row g-1 align-items-center">
                <div class="col-4">
                    <input v-model="c1" type="color" class="form-control form-control-color w-100" @input="applyGradient" />
                </div>
                <div class="col-4">
                    <input v-model="c2" type="color" class="form-control form-control-color w-100" @input="applyGradient" />
                </div>
                <div class="col-4">
                    <input v-model.number="angle" type="number" min="0" max="360" class="form-control form-control-sm" title="Angle °" @input="applyGradient" />
                </div>
            </div>
        </div>
    </div>
</template>
