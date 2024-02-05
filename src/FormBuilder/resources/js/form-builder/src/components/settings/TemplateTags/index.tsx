import { Ref, useState } from "react";
import { useCopyToClipboard } from "@wordpress/compose";
import { __ } from "@wordpress/i18n";
import { copy as copyIcon } from "@wordpress/icons";
import { Button } from "@wordpress/components";

import "./styles.scss";

/**
 * @since 3.3.0
 */
function CopyTagButton({textToCopy}) {
    const [isCopied, setCopied] = useState(false);
    const ref = useCopyToClipboard(textToCopy, () => {
        setCopied(true);

        return setTimeout(() => setCopied(false), 1000);
    });

    return (
        <Button
            className="givewp-template-tags__copy-button"
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
 * @since 3.3.0
 */
export default function TemplateTags({
    templateTags,
    templateTagsRef,
}: {
    templateTags: TemplateTag[];
    templateTagsRef?: Ref<HTMLUListElement>;
}) {
    return (
        <ul className="givewp-template-tags__list" ref={templateTagsRef}>
            {templateTags.map(({id, description}) => {
                const tagId = `{${id}}`;

                return (
                    <li className="givewp-template-tags__list-item" key={id}>
                        <div className="givewp-template-tags__list-item-top">
                            <span className="givewp-template-tags__tag">{tagId}</span>
                            <CopyTagButton textToCopy={tagId} />
                        </div>
                        <div className="givewp-template-tags__list-item-bottom">
                            <span className="givewp-template-tags__description">{description}</span>
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}

/**
 * @since 3.3.0
 */
export type TemplateTag = {
    id: string;
    description: string;
};
