/**
 * External dependencies
 */
import classnames from 'classnames';
import {TextareaHTMLAttributes} from 'react';
import {useFormContext} from 'react-hook-form';

/**
 * Internal dependencies
 */
import './styles.scss';

type TextareaControlProps = TextareaHTMLAttributes<HTMLTextAreaElement> & {
    name: string;
    className?: string;
    help?: string;
};

/**
 * @unreleased
 */
function TextareaControl({name, help, maxLength, className, ...rest}: TextareaControlProps) {
    const {register, watch} = useFormContext();
    const value = watch(name);

    return (
            <div className={classnames('givewp-textarea-control', className)}>
                <textarea
                    {...register(name)}
                    className="givewp-textarea-control__textarea"
                    maxLength={maxLength}
                    {...rest}
                />
                {help && <p className="givewp-textarea-control__help">{help}</p>}
                {maxLength > 0 && (
                    <span className="givewp-textarea-control__counter">
                        {value.length ?? 0}/{maxLength}
                    </span>
                )}
            </div>
    );
}

export default TextareaControl;
