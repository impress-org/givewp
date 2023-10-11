import { KeyboardShortcuts } from "@wordpress/components";
import {undo, redo} from '@givewp/form-builder/stores/form-state/reducer';
import {useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

export default function UndoRedoShortcuts() {

    const dispatch = useFormStateDispatch();

    const onUndo = () => dispatch(undo())
    const onRedo = () => dispatch(redo())

    return <KeyboardShortcuts
        bindGlobal={true}
        shortcuts={{
            'mod+z': onUndo,
            'mod+shift+z': onRedo,
        }}
    />
}
