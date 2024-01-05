import { Ref, useState } from "react";
import { useCopyToClipboard } from "@wordpress/compose";
import { __ } from "@wordpress/i18n";
import { copy as copyIcon } from "@wordpress/icons";
import { Button } from "@wordpress/components";

import "./styles.scss";

/**
 * @unreleased
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
 * @unreleased
 */
export default function TemplateTags({templateTags}: {templateTags: TemplateTag[]}) {
    return (
        <ul className="givewp-template-tags__list">
            {templateTags.map(({ id, description }) => {
                const tagId = `{${id}}`;

                return (
                    <li className="givewp-template-tags__list-item" key={id}>
                        <div className="givewp-template-tags__list-item-top">
                            <span className="givewp-template-tags__tag">{tagId}</span>
                            <CopyTagButton textToCopy={tagId} />
                        </div>
                        <div className="givewp-template-tags__list-item-bottom">
                                <span className="givewp-template-tags__description">
                                    {description}
                                </span>
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}

/**
 * @unreleased
 */
export type TemplateTag = {
    id: string;
    description: string;
};
