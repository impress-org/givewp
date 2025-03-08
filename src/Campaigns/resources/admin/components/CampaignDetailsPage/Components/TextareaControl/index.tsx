/**
 * External dependencies
 */
import classnames from 'classnames';
import {TextareaHTMLAttributes} from 'react';
import {Controller, useFormContext} from 'react-hook-form';

/**
 * Internal dependencies
 */
import './styles.scss';

type TextareaControlProps = TextareaHTMLAttributes<HTMLTextAreaElement> & {
    name: string;
    help?: string;
    className?: string;
};

/**
 * @unreleased
 */
function TextareaControl({name, help, maxLength, className, ...rest}: TextareaControlProps) {
    const {control} = useFormContext();

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <div className={classnames('givewp-textarea-control', className)}>
                    <textarea
                        {...field}
                        className="givewp-textarea-control__textarea"
                        maxLength={maxLength}
                        onChange={(e) => {
                            let newValue = e.target.value;

                            if (typeof maxLength === 'number' && maxLength > 0) {
                                newValue = newValue.slice(0, maxLength);
                            }

                            field.onChange(newValue);
                        }}
                        {...rest}
                    />
                    {help && <p className="givewp-textarea-control__help">{help}</p>}
                    {typeof maxLength === 'number' && maxLength > 0 && (
                        <span className="givewp-textarea-control__counter">
                            {field.value?.length ?? 0}/{maxLength}
                        </span>
                    )}
                </div>
            )}
        />
    );
}

export default TextareaControl;
