import {useSelect} from '@wordpress/data';

/**
 * @unreleased
 */
export default function usePostState(): { isSaving: boolean, isDisabled: boolean } {
    const isSaving = useSelect((select) => {
        return select('core/editor').isSavingPost<boolean>()
    }, []);

    const isDisabled = useSelect((select) => {
        return !select('core/editor').isEditedPostPublishable()
    }, []);

    return {
        isSaving,
        isDisabled
    }
}
