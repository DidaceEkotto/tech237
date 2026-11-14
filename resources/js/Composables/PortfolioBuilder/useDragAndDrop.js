import { ref } from 'vue';

export function useDragAndDrop() {
    const dragPayload = ref(null); // { kind: 'new-module'|'move-module', moduleType?, moduleId? }
    const activeDropColumnId = ref(null);

    function startNewModuleDrag(moduleType) {
        dragPayload.value = { kind: 'new-module', moduleType };
    }

    function startMoveModuleDrag(moduleId) {
        dragPayload.value = { kind: 'move-module', moduleId };
    }

    function clearDrag() {
        dragPayload.value = null;
        activeDropColumnId.value = null;
    }

    return {
        dragPayload,
        activeDropColumnId,
        startNewModuleDrag,
        startMoveModuleDrag,
        clearDrag,
    };
}
