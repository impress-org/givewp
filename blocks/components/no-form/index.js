/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n';
import {Button} from '@wordpress/components';

/**
 * Internal dependencies
 */
import {getSiteUrl} from '../../utils';
import GiveBlankSlate from '../blank-slate';

/**
 * Render No forms Found UI
 *
 * @unreleased Replace "new form" with "new campaign form" link
 */

const NoForms = () => {
    return (
        <GiveBlankSlate
            title={__('No campaign forms found.', 'give')}
            description={__('The first step towards accepting online donations is to create a campaign.', 'give')}
            helpLink
        >
            <Button
                isPrimary
                isLarge
                className="give-blank-slate__cta"
                href={`${getSiteUrl()}/wp-admin/edit.php?post_type=give_forms&page=give-campaigns&new=campaign`}
            >
                {__('Create Campaign Form', 'give')}
            </Button>
        </GiveBlankSlate>
    );
};

export default NoForms;
