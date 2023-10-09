import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, description, placeholder, defaultValue} = attributes;
    const requiredClass = isRequired ? 'give-is-required' : '';

    return (
        <>
            <div>
                <span
                    className={classnames('components-input-control__label', 'give-text-block__label', requiredClass)}
                >
                    {label}
                </span>
                {description && <p className="give-text-block__description">{description}</p>}
                <input type="text" placeholder={placeholder} readOnly onChange={null} value={defaultValue} />
            </div>
        </>
    );
}
