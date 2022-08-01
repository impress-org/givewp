import {__} from '@wordpress/i18n';
import usePostState from '../hooks/usePostState';
import {dispatch} from '@wordpress/data';

const savePost = () => dispatch('core/editor').savePost();

export default function ConfirmButton() {
    const {isSaving, isDisabled} = usePostState();

    return <div className="givewp-form-block__submit-button--container">
        <button
            className="givewp-form-block__submit-button"
            type="button"
            disabled={isSaving || isDisabled}
            onClick={savePost}>
            {__('Confirm', 'give')}
        </button>
    </div>
}
