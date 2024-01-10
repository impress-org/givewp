import { Button, Popover, TextareaControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { close as closeIcon } from "@wordpress/icons";
import { ClassicEditor } from "@givewp/form-builder-library";
import "./styles.scss";
import TemplateTags, { TemplateTag } from "@givewp/form-builder/components/settings/TemplateTags";

/**
 * @since 3.0.0
 */
type PopoverContentWithTemplateTagsProps = {
    onClose?(): void;
    content: string;
    templateTags: TemplateTag[];
    onContentChange?(content: string): void;
    heading: string;
    richText?: boolean;
};

/**
 * @since 3.3.0 extracted template tags to be a shared component
 * @since 3.0.0
 */
export default function PopoverContentWithTemplateTags({
    content,
    onContentChange,
    onClose,
    templateTags,
    heading,
    richText,
}: PopoverContentWithTemplateTagsProps) {
    return (
        <Popover className="givewp-popover-content-settings" resize={false} shift placement="left" offset={30}>
            <div className="givewp-popover-content-settings__header">
                <div className="givewp-popover-content-settings__heading">
                    <span>{heading}</span>
                </div>
                <Button icon={closeIcon} className="givewp-popover-content-settings__close-button" onClick={onClose} />
            </div>
            {richText ? (
                <ClassicEditor
                    id={'givewp-popover-content-with-template-tags'}
                    label={__('', 'give')}
                    content={content}
                    setContent={(newContent) => {
                        onContentChange(newContent);
                    }}
                />
            ) : (
                <TextareaControl
                    className="givewp-popover-content-settings__textarea"
                    value={content}
                    onChange={(newContent) => {
                        onContentChange(newContent);
                    }}
                />
            )}
            <div className="givewp-popover-content-settings__template-tags-heading">
                <span>{__('Template tags', 'give')}</span>
            </div>

            <div className="givewp-popover-content-settings__template-tags">
                <TemplateTags templateTags={templateTags} />
            </div>
        </Popover>
    );
}
