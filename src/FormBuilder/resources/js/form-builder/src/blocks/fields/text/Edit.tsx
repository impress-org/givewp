import classnames from 'classnames';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, description, placeholder, defaultValue} = attributes;

    return (
        <div className={classnames({'give-is-required': isRequired})}>
            <span className="components-input-control__label give-text-block__label">{label}</span>
            {description && <p className="give-text-block__description">{description}</p>}
            <input type="text" placeholder={placeholder} readOnly onChange={null} value={defaultValue} />
        </div>
    );
}
