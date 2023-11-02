import {KeyboardShortcuts} from "@wordpress/components";
import {undo, redo} from '@givewp/form-builder/stores/form-state/reducer';
import {useFormState, useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

/**
 * Register keyboard shortcuts for the undoable reducer state.
 * @unreleased
 */
export default function UndoRedoShortcuts() {

    const dispatch = useFormStateDispatch();
    const {canUndo, canRedo} = useFormState();

    const onUndo = () => canUndo && dispatch(undo())
    const onRedo = () => canRedo && dispatch(redo())

    return <KeyboardShortcuts
        bindGlobal={true}
        shortcuts={{
            'mod+z': onUndo,
            'mod+shift+z': onRedo,
        }}
    />
}
