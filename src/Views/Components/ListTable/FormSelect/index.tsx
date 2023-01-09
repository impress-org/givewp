import styles from './FormSelect.module.scss';
import cx from 'classnames';
import Input from '@givewp/components/ListTable/Input';

export const FormSelect = ({options, name, placeholder = '', ariaLabel = '', onChange, ...rest}) => {
    return (
        <>
            <Input
                type="search"
                className={cx(styles.formSelect)}
                list={`giveSearchSelect-${name}`}
                onChange={updateSearchableSelect(options, name, onChange)}
                autoComplete={'off'}
                aria-label={ariaLabel}
                placeholder={placeholder}
                {...rest}
            />
            <datalist id={`giveSearchSelect-${name}`} onChange={onChange}>
                {options.map(({value, text}) => (
                    <option key={`${value}${text}`} value={value === '0' ? text : `${text} (#${value})`} />
                ))}
            </datalist>
        </>
    );
};

const updateSearchableSelect = (options, name, onChange) => {
    return (event) => {
        if (event.target.value === '') {
            onChange(name, 0);
        }
        const selectedIndex = options.findIndex((option) => {
            return event.target.value.endsWith(`(#${option.value})`);
        });
        if (selectedIndex > -1) {
            onChange(name, options[selectedIndex].value);
        }
    };
};
