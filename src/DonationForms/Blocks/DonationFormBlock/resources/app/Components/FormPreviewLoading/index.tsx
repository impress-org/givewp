import Classic from './Classic';
import MultiStep from './MultiStep';
import Basic from './Basic';

import './index.scss';

type FormPreviewLoadingProps = {
    design?: 'classic' | 'multi-step' | string;
    isLoading?: boolean;
}

const getDesignPlaceholder = (design: string) => {
    switch (design) {
        case 'classic':
            return <Classic />
        case 'multi-step':
            return <MultiStep />
        default :
            return <Basic />
    }
}

export default function FormPreviewLoading({design, isLoading}: FormPreviewLoadingProps) {
    return (
        <div className="givewp__form-preview" style={{opacity: isLoading ? 1 : 0}}>
            {getDesignPlaceholder(design)}
        </div>
    );
}

