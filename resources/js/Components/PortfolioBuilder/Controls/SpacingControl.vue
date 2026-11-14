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
    <div class="pb-spacing-control">
        <div class="small fw-semibold text-secondary mb-2">
            Espacements ({{ viewport }})
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Padding Haut</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('paddingTop')" placeholder="24px" @change="setVal('paddingTop', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Padding Bas</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('paddingBottom')" placeholder="24px" @change="setVal('paddingBottom', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Padding Gauche</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('paddingLeft')" placeholder="16px" @change="setVal('paddingLeft', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Padding Droite</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('paddingRight')" placeholder="16px" @change="setVal('paddingRight', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Marge Bas</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('marginBottom')" placeholder="0px" @change="setVal('marginBottom', $event.target.value)" />
            </div>
            <div class="col-6">
                <label class="form-label xsmall text-muted mb-0">Arrondi (Radius)</label>
                <input type="text" class="form-control form-control-sm" :value="getVal('borderRadius')" placeholder="16px" @change="setVal('borderRadius', $event.target.value)" />
            </div>
        </div>
    </div>
</template>
