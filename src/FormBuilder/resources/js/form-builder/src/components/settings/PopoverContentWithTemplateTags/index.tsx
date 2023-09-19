import {Button, Popover, TextareaControl} from '@wordpress/components';
import {useCopyToClipboard} from '@wordpress/compose';
import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import type {Ref} from 'react';
import {close as closeIcon, copy as copyIcon} from '@wordpress/icons';
import './styles.scss';
import Editor from '@givewp/form-builder/components/editor';

/**
 * @since 3.0.0
 */
type TemplateTag = {
    id: string;
    description: string;
};

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
 * @since 3.0.0
 */
function CopyTagButton({textToCopy}) {
    const [isCopied, setCopied] = useState(false);
    const ref = useCopyToClipboard(textToCopy, () => {
        setCopied(true);

        return setTimeout(() => setCopied(false), 1000);
    });

    return (
        <Button
            className="givewp-popover-content-settings__copy-button"
            isSmall
            variant="tertiary"
            ref={ref as Ref<HTMLAnchorElement>}
            icon={copyIcon}
        >
            {isCopied ? __('Copied!', 'give') : __('Copy Tag', 'give')}
        </Button>
    );
}

/**
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
                <Editor
                    className="givewp-popover-content-settings__editor"
                    value={content}
                    onChange={(newContent) => {
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

            <ul className="givewp-popover-content-settings__template-tags-list">
                {templateTags.map(({id, description}) => {
                    const tagId = `{${id}}`;

                    return (
                        <li className="givewp-popover-content-settings__template-tag-list-item" key={id}>
                            <div className="givewp-popover-content-settings__template-tag-list-item-top">
                                <span className="givewp-popover-content-settings__template-tag">{tagId}</span>
                                <CopyTagButton textToCopy={tagId} />
                            </div>
                            <div className="givewp-popover-content-settings__template-tag-list-item-bottom">
                                <span className="givewp-popover-content-settings__template-description">
                                    {description}
                                </span>
                            </div>
                        </li>
                    );
                })}
            </ul>
        </Popover>
    );
}
