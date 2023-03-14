import {Controller, useFormContext, useWatch} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';
import cx from 'classnames';
import styles from './style.module.scss';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';

export default function StatusSelector({options}) {
    const {control} = useFormContext();

    const status = useWatch({
        name: 'status',
    });

    const defaultStatusByValue = options.find((option) => option['value'] === status);

    const customOptionLabel = ({label, value}) => (
        <div className={styles.status}>
            <span className={cx(styles.ellipse, styles[value])} />
            <span>{label}</span>
        </div>
    );

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                <DownArrowIcon />
            </components.DropdownIndicator>
        );
    };

    const styleConfig = {
        control: (provided, state) => ({
            ...provided,
            display: 'flex',
            alignItems: 'center',
            gap: 12,
            border: 'none',
            width: 165,
            height: 32,
            background: '#fff',
            borderRadius: 2,
        }),
        indicatorsContainer: (provided, state) => ({
            ...provided,
            background: '#F2F2F2',
            height: '100%',
            borderRadius: 2,
        }),
        valueContainer: (provided, state) => ({
            ...provided,
            background: '#F2F2F2',
            height: '100%',
            borderRadius: 2,
        }),
    };
    return (
        <div className={styles.statusSelectorContainer}>
            <Controller
                control={control}
                name="status"
                render={({
                    field: {onChange, onBlur, value, name, ref},
                    fieldState: {invalid, isTouched, isDirty, error},
                    formState,
                }) => (
                    <ReactSelect
                        name={name}
                        defaultValue={defaultStatusByValue}
                        options={options}
                        onChange={onChange}
                        isClearable={false}
                        isSearchable={false}
                        placeholder={''}
                        onBlur={onBlur}
                        formatOptionLabel={customOptionLabel}
                        components={{
                            IndicatorSeparator: () => null,
                            DropdownIndicator,
                        }}
                        styles={styleConfig}
                    />
                )}
            />
        </div>
    );
}
