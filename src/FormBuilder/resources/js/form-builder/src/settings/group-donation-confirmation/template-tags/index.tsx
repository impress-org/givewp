import {__} from '@wordpress/i18n';
import {useCopyToClipboard} from '@wordpress/compose';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {Ref, useState} from 'react';
import {Button} from '@wordpress/components';
import {copy as copyIcon} from '@wordpress/icons';

import './styles.scss';

const {donationConfirmationTemplateTags} = getFormBuilderWindowData();

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

const TemplateTags = () => {
    return (
        <div className={'givewp-form-settings__section__body__extra-gap'}>
            <ul className="givewp-donation-confirmation-settings__template-tags-list">
                {donationConfirmationTemplateTags.map(({id, description}) => {
                    const tagId = `{${id}}`;

                    return (
                        <li className="givewp-donation-confirmation-settings__template-tag-list-item" key={id}>
                            <div className="givewp-donation-confirmation-settings__template-tag-list-item-top">
                                <span className="givewp-donation-confirmation-settings__template-tag">{tagId}</span>
                                <CopyTagButton textToCopy={tagId} />
                            </div>
                            <div className="givewp-donation-confirmation-settings__template-tag-list-item-bottom">
                                <span className="givewp-donation-confirmation-settings__template-description">
                                    {description}
                                </span>
                            </div>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
};

export default TemplateTags;
