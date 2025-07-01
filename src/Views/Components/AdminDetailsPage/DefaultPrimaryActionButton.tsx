/**
 * External Dependencies
 */
import { __ } from '@wordpress/i18n';
import {Spinner} from '@wordpress/components';

/**
 * @since 4.4.0
 */
export default function DefaultPrimaryActionButton({ isSaving, formState, className }: { isSaving: boolean, formState: any, className: string   }) {
    return (
        <button
            type="submit"
            disabled={!formState.isDirty}
            className={className}
        >
            {isSaving ? (
                <>
                    {__('Saving changes', 'give')}
                    <Spinner />
                </>
            ) : __('Save changes', 'give')}
        </button>
    );
}
