import { ref, computed } from 'vue';

export function useUndoRedo(initialValue = null, maxHistory = 40) {
    const history = ref(initialValue ? [JSON.stringify(initialValue)] : []);
    const pointer = ref(initialValue ? 0 : -1);

    const canUndo = computed(() => pointer.value > 0);
    const canRedo = computed(() => pointer.value < history.value.length - 1);

    function reset(value) {
        history.value = [JSON.stringify(value)];
        pointer.value = 0;
    }

    function pushSnapshot(value) {
        const serialized = JSON.stringify(value);
        if (pointer.value >= 0 && history.value[pointer.value] === serialized) {
            return;
        }
        if (pointer.value < history.value.length - 1) {
            history.value = history.value.slice(0, pointer.value + 1);
        }
        history.value.push(serialized);
        if (history.value.length > maxHistory) {
            history.value.shift();
        }
        pointer.value = history.value.length - 1;
    }

    function undo() {
        if (!canUndo.value) return null;
        pointer.value -= 1;
        return JSON.parse(history.value[pointer.value]);
    }

    function redo() {
        if (!canRedo.value) return null;
        pointer.value += 1;
        return JSON.parse(history.value[pointer.value]);
    }

    return {
        canUndo,
        canRedo,
        reset,
        pushSnapshot,
        undo,
        redo,
    };
}
