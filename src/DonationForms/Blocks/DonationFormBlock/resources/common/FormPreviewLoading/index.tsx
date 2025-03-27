import {Spinner} from '@givewp/components';
import Classic from './placeholders/Classic';
import MultiStep from './placeholders/MultiStep';

import './index.scss';

type FormPreviewLoadingProps = {
    design?: 'classic' | 'multi-step' | string;
}

const getDesignPlaceholder = (design: string) => {
    switch (design) {
        case 'classic':
            return <Classic />
        case 'multi-step':
            return <MultiStep />
        default :
            return <Classic />
    }
}

export default function FormPreviewLoading({design}: FormPreviewLoadingProps) {
    return (
        <div className="givewp__form-preview">
            <div className="givewp__form-preview__container">
                <div className="givewp__form-preview__spinner">
                    <Spinner />
                </div>
                {getDesignPlaceholder(design)}
            </div>
        </div>
    );
}

