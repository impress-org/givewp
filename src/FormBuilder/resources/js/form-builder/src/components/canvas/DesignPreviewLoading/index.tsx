import {__} from '@wordpress/i18n';
import {Spinner} from '@wordpress/components';
import Classic from './placeholders/Classic';
import MultiStep from './placeholders/MultiStep';

import './index.scss';

export default function DesignPreviewLoading({design, editing, designUpdated}) {
    const getDesignPlaceholder = (design: string) => {
        switch (design) {
            case 'classic':
                return <Classic />
            case 'multi-step':
                return <MultiStep />
        }

        return null;
    }

    return (
        <div className="givewp__component-DesignPreview">
            <div className="givewp__component-DesignPreview-spinner">
                <Spinner />
                {editing ? __('Updating your changes', 'give') : __('Preparing your form design', 'give')}...
            </div>
            {designUpdated && (
                <div className="givewp__component-DesignPreview-container">
                    {getDesignPlaceholder(design)}
                </div>
            )}
        </div>
    );
}

