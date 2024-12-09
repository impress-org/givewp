import {__} from '@wordpress/i18n';
import {TriangleIcon} from '@givewp/campaigns/admin/components/Icons';

export default ({handleClick}) => (
    <>
        <TriangleIcon />
        <span>
            {__("Your campaign is currently archived. You can view the campaign details but won't be able to make any changes until it's moved out of archive.", 'give')}
        </span>
        <strong>
            <a href="#" onClick={() => handleClick()}>
                {__('Move to draft', 'give')}
            </a>
        </strong>
    </>
)
