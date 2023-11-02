import type {FormSettings} from './formSettings';
import {BlockInstance} from '@wordpress/blocks';
import EditorMode from "@givewp/form-builder/types/editorMode";

/**
 * @unreleased Add EditorMode type
 * @since 3.0.0
 */
export type FormState = {
    blocks: BlockInstance[];
    settings: FormSettings;
    isDirty?: boolean;
    canUndo?: boolean;
    canRedo?: boolean;
    editorMode: EditorMode;
};
