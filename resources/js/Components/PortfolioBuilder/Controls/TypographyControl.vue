<script setup>
import FontPickerControl from './FontPickerControl.vue';
import ColorGradientControl from './ColorGradientControl.vue';

const props = defineProps({
    styles: { type: Object, required: true },
    viewport: { type: String, default: 'desktop' },
});

const emit = defineEmits(['change']);

function getVal(key) {
    return props.styles?.[props.viewport]?.[key] || '';
}

function setVal(key, val) {
    if (!props.styles[props.viewport]) {
        props.styles[props.viewport] = {};
    }
    props.styles[props.viewport][key] = val;
    emit('change');
}
</script>

<template>
    <div class="pb-typography-control">
        <div class="small fw-semibold text-secondary mb-2">
            Typographie &amp; Couleurs ({{ viewport }})
        </div>

        <FontPickerControl
            :model-value="getVal('fontFamily')"
            @update:modelValue="(v) => setVal('fontFamily', v)"
        />

        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Taille (px)</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('fontSize')" placeholder="16px" @change="setVal('fontSize', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Graisse</label>
                <select class="form-select form-select-sm" :value="getVal('fontWeight')" @change="setVal('fontWeight', $event.target.value)">
                    <option value="">Défaut</option>
                    <option value="400">Normal (400)</option>
                    <option value="500">Medium (500)</option>
                    <option value="600">Semi-Bold (600)</option>
                    <option value="700">Bold (700)</option>
                    <option value="800">Extra-Bold (800)</option>
                </select>
            </div>
        </div>

        <ColorGradientControl
            :model-value="getVal('color') || '#0f172a'"
            label="Couleur du texte"
            :allow-gradient="false"
            @update:modelValue="(v) => setVal('color', v)"
        />

        <ColorGradientControl
            :model-value="getVal('background') || getVal('backgroundColor') || ''"
            label="Arrière-plan (Couleur ou Dégradé)"
            :allow-gradient="true"
            @update:modelValue="(v) => setVal('background', v)"
        />
    </div>
</template>
