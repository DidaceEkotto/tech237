<script setup>
import { onMounted, watch } from 'vue';
import { WEB_FONTS_30, ensureFontLoaded } from '@/Composables/PortfolioBuilder/fontsAndAnimations';

const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: 'Police d’écriture (30 polices)' },
});

const emit = defineEmits(['update:modelValue']);

onMounted(() => {
    WEB_FONTS_30.slice(0, 12).forEach(ensureFontLoaded);
    if (props.modelValue) ensureFontLoaded(props.modelValue);
});

watch(
    () => props.modelValue,
    (val) => {
        if (val) ensureFontLoaded(val);
    }
);
</script>

<template>
    <div class="mb-2">
        <label class="form-label xsmall text-muted mb-1">{{ label }}</label>
        <select
            class="form-select form-select-sm"
            :value="modelValue"
            :style="{ fontFamily: modelValue || 'inherit' }"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="">Police par défaut</option>
            <option
                v-for="f in WEB_FONTS_30"
                :key="f"
                :value="f"
                :style="{ fontFamily: f }"
            >
                {{ f }} — Aperçu AaBbCc 123
            </option>
        </select>
        <div
            v-if="modelValue"
            class="mt-1 p-2 rounded-2 bg-light border small text-truncate"
            :style="{ fontFamily: modelValue }"
        >
            Aperçu : {{ modelValue }} — Constructeur Tech237
        </div>
    </div>
</template>
