import {__} from '@wordpress/i18n';
import {TriangleIcon} from '@givewp/campaigns/admin/components/Icons';

export default ({handleClick}) => (
    <>
        <TriangleIcon />
        <span>
            {__(
                'Your campaign is currently archived and can not be edited. Moving this campaign out of archive will require you to update your landing page and campaign forms.',
                'give'
            )}
        </span>
        <strong>
            <a href="#" onClick={() => handleClick()}>
                {__('Move to Publish', 'give')}
            </a>
        </strong>
    </>
);
