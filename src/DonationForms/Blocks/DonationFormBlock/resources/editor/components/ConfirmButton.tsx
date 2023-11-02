import {__} from '@wordpress/i18n';
import usePostState from '../hooks/usePostState';
import {dispatch} from '@wordpress/data';

import '../styles/index.scss';

// @ts-ignore
const savePost = () => dispatch('core/editor').savePost();

export default function ConfirmButton({formId, enablePreview}) {
    const {isSaving, isDisabled} = usePostState();

    return (
        <button
            className="givewp-donation-form-selector__submit"
            type="button"
            disabled={isSaving || isDisabled || !formId}
            onClick={() => {
                enablePreview();
                return savePost();
            }}
        >
            {__('Confirm', 'give')}
        </button>
    );
}
