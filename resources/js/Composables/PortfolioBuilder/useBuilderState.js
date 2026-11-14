import { ref, computed } from 'vue';
import { createModule, createRow, createSection, uid } from './moduleRegistry';
import { useUndoRedo } from './useUndoRedo';

export function useBuilderState(initialTree) {
    const normalized = initialTree && Array.isArray(initialTree.sections)
        ? JSON.parse(JSON.stringify(initialTree))
        : { schema_version: 1, sections: [] };

    const tree = ref(normalized);
    const selected = ref(null); // { kind: 'section'|'row'|'column'|'module', id: string }
    const isDirty = ref(false);

    const history = useUndoRedo(normalized);

    function commitChange() {
        isDirty.value = true;
        history.pushSnapshot(tree.value);
    }

    function loadTree(newTree) {
        const clone = newTree && Array.isArray(newTree.sections)
            ? JSON.parse(JSON.stringify(newTree))
            : { schema_version: 1, sections: [] };
        tree.value = clone;
        selected.value = null;
        isDirty.value = false;
        history.reset(clone);
    }

    function undo() {
        const prev = history.undo();
        if (prev) {
            tree.value = prev;
            isDirty.value = true;
        }
    }

    function redo() {
        const next = history.redo();
        if (next) {
            tree.value = next;
            isDirty.value = true;
        }
    }

    function selectNode(kind, id) {
        selected.value = { kind, id };
    }

    function clearSelection() {
        selected.value = null;
    }

    const selectedNodeData = computed(() => {
        if (!selected.value) return null;
        const { kind, id } = selected.value;
        for (const sec of tree.value.sections) {
            if (kind === 'section' && sec.id === id) return { kind, node: sec };
            for (const row of sec.rows || []) {
                if (kind === 'row' && row.id === id) return { kind, node: row, section: sec };
                for (const col of row.columns || []) {
                    if (kind === 'column' && col.id === id) return { kind, node: col, row, section: sec };
                    for (const mod of col.modules || []) {
                        if (kind === 'module' && mod.id === id) return { kind, node: mod, column: col, row, section: sec };
                    }
                }
            }
        }
        return null;
    });

    function addSection(moduleType = null, atIndex = null) {
        const sec = createSection(moduleType);
        if (atIndex === null || atIndex >= tree.value.sections.length) {
            tree.value.sections.push(sec);
        } else {
            tree.value.sections.splice(Math.max(0, atIndex), 0, sec);
        }
        if (moduleType && sec.rows[0]?.columns[0]?.modules[0]) {
            selectNode('module', sec.rows[0].columns[0].modules[0].id);
        } else {
            selectNode('section', sec.id);
        }
        commitChange();
        return sec;
    }

    function duplicateSection(sectionId) {
        const idx = tree.value.sections.findIndex((s) => s.id === sectionId);
        if (idx === -1) return;
        const copy = cloneWithNewIds(tree.value.sections[idx]);
        tree.value.sections.splice(idx + 1, 0, copy);
        selectNode('section', copy.id);
        commitChange();
    }

    function deleteSection(sectionId) {
        tree.value.sections = tree.value.sections.filter((s) => s.id !== sectionId);
        if (selected.value?.id === sectionId) selected.value = null;
        commitChange();
    }

    function moveSection(sectionId, direction) {
        const idx = tree.value.sections.findIndex((s) => s.id === sectionId);
        const targetIdx = idx + direction;
        if (idx === -1 || targetIdx < 0 || targetIdx >= tree.value.sections.length) return;
        const [item] = tree.value.sections.splice(idx, 1);
        tree.value.sections.splice(targetIdx, 0, item);
        commitChange();
    }

    function addRow(sectionId, columnsCount = 1) {
        const sec = tree.value.sections.find((s) => s.id === sectionId);
        if (!sec) return;
        const row = createRow(columnsCount);
        sec.rows.push(row);
        selectNode('row', row.id);
        commitChange();
        return row;
    }

    function deleteRow(sectionId, rowId) {
        const sec = tree.value.sections.find((s) => s.id === sectionId);
        if (!sec) return;
        sec.rows = sec.rows.filter((r) => r.id !== rowId);
        if (selected.value?.id === rowId) selected.value = null;
        commitChange();
    }

    function addModuleToColumn(columnId, moduleType, atIndex = null) {
        const mod = createModule(moduleType);
        for (const sec of tree.value.sections) {
            for (const row of sec.rows || []) {
                for (const col of row.columns || []) {
                    if (col.id === columnId) {
                        if (atIndex === null) {
                            col.modules.push(mod);
                        } else {
                            col.modules.splice(atIndex, 0, mod);
                        }
                        selectNode('module', mod.id);
                        commitChange();
                        return mod;
                    }
                }
            }
        }
        return null;
    }

    function duplicateModule(moduleId) {
        for (const sec of tree.value.sections) {
            for (const row of sec.rows || []) {
                for (const col of row.columns || []) {
                    const idx = col.modules.findIndex((m) => m.id === moduleId);
                    if (idx !== -1) {
                        const copy = cloneWithNewIds(col.modules[idx]);
                        col.modules.splice(idx + 1, 0, copy);
                        selectNode('module', copy.id);
                        commitChange();
                        return;
                    }
                }
            }
        }
    }

    function deleteModule(moduleId) {
        for (const sec of tree.value.sections) {
            for (const row of sec.rows || []) {
                for (const col of row.columns || []) {
                    const before = col.modules.length;
                    col.modules = col.modules.filter((m) => m.id !== moduleId);
                    if (col.modules.length !== before) {
                        if (selected.value?.id === moduleId) selected.value = null;
                        commitChange();
                        return;
                    }
                }
            }
        }
    }

    function moveModule(moduleId, targetColumnId, targetIndex = null) {
        let extracted = null;
        for (const sec of tree.value.sections) {
            for (const row of sec.rows || []) {
                for (const col of row.columns || []) {
                    const idx = col.modules.findIndex((m) => m.id === moduleId);
                    if (idx !== -1) {
                        [extracted] = col.modules.splice(idx, 1);
                        break;
                    }
                }
                if (extracted) break;
            }
            if (extracted) break;
        }
        if (!extracted) return;

        for (const sec of tree.value.sections) {
            for (const row of sec.rows || []) {
                for (const col of row.columns || []) {
                    if (col.id === targetColumnId) {
                        if (targetIndex === null || targetIndex > col.modules.length) {
                            col.modules.push(extracted);
                        } else {
                            col.modules.splice(Math.max(0, targetIndex), 0, extracted);
                        }
                        selectNode('module', extracted.id);
                        commitChange();
                        return;
                    }
                }
            }
        }
    }

    function insertTemplateSection(sectionPayload) {
        const cloned = cloneWithNewIds(sectionPayload);
        tree.value.sections.push(cloned);
        selectNode('section', cloned.id);
        commitChange();
    }

    function cloneWithNewIds(node) {
        const copy = JSON.parse(JSON.stringify(node));
        const assignIds = (obj, prefix = 'node') => {
            if (!obj || typeof obj !== 'object') return;
            if (Array.isArray(obj)) {
                obj.forEach((item) => assignIds(item, prefix));
                return;
            }
            if (typeof obj.id === 'string' && obj.id.includes('_')) {
                const p = obj.id.split('_')[0] || prefix;
                obj.id = uid(p);
            }
            Object.values(obj).forEach((v) => {
                if (v && typeof v === 'object') assignIds(v, prefix);
            });
        };
        assignIds(copy);
        return copy;
    }

    return {
        tree,
        selected,
        selectedNodeData,
        isDirty,
        canUndo: history.canUndo,
        canRedo: history.canRedo,
        commitChange,
        loadTree,
        undo,
        redo,
        selectNode,
        clearSelection,
        addSection,
        duplicateSection,
        deleteSection,
        moveSection,
        addRow,
        deleteRow,
        addModuleToColumn,
        duplicateModule,
        deleteModule,
        moveModule,
        insertTemplateSection,
    };
}
