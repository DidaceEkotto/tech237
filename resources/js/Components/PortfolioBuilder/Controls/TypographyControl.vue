<script setup>
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
            Typographie ({{ viewport }})
        </div>
        <div class="row g-2">
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
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Couleur texte</label>
                <input type="color" class="form-control form-control-color w-100" :value="getVal('color') || '#0f172a'" @input="setVal('color', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Fond</label>
                <input type="color" class="form-control form-control-color w-100" :value="getVal('backgroundColor')?.startsWith('#') ? getVal('backgroundColor') : '#ffffff'" @input="setVal('backgroundColor', $event.target.value)" />
            </div>
        </div>
    </div>
</template>
