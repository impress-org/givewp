import {TextareaControl} from '@wordpress/components';
import './styles.scss';
import Editor from '@givewp/form-builder/components/editor';

/**
 * @unreleased
 */
export default function TextEditor({
    content,
    onChange,
    richText,
}: TextEditorProps) {
    return (
        <div className="givewp-settings-text-editor">
            {richText ? (
                <Editor
                    className="givewp-settings-text-editor__editor"
                    value={content}
                    onChange={(newContent) => {
                        onChange(newContent);
                    }}
                />
            ) : (
                <TextareaControl
                    className="givewp-settings-text-editor__textarea"
                    value={content}
                    onChange={(newContent) => {
                        onChange(newContent);
                    }}
                />
            )}
        </div>
    );
}

/**
 * @unreleased
 */
type TextEditorProps = {
    content: string;
    onChange?(content: string): void;
    richText?: boolean;
};
