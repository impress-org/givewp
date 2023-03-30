import {Controller, useFormContext, useWatch} from 'react-hook-form';
import ReactSelect, {components} from 'react-select';
import cx from 'classnames';
import styles from './style.module.scss';
import DownArrowIcon from '@givewp/components/AdminUI/Icons/DownArrowIcon';
import {StyleConfig} from './StyleConfig';

/**
 *
 * @unreleased
 */

export type StatusSelector = {
    options: Array<{
        value: string;
        label: string;
    }>;
};

export default function StatusSelector({options}: StatusSelector) {
    const {control} = useFormContext();

    const formFieldStatusValue = useWatch({
        name: 'status',
    });

    const defaultStatusByValue = options.find((option) => option['value'] === formFieldStatusValue);

    const customOptionLabel = ({label, value}) => (
        <div className={styles.status}>
            <span className={cx(styles.ellipse, styles[value])} />
            <span>{label}</span>
        </div>
    );

    const DropdownIndicator = (props) => {
        return (
            <components.DropdownIndicator {...props}>
                <DownArrowIcon color={'#0e0e0e'} />
            </components.DropdownIndicator>
        );
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
                        onChange={(option) => {
                            onChange(option.value);
                        }}
                        isClearable={false}
                        isSearchable={false}
                        placeholder={''}
                        formatOptionLabel={customOptionLabel}
                        components={{
                            IndicatorSeparator: () => null,
                            DropdownIndicator,
                        }}
                        styles={StyleConfig}
                    />
                )}
            />
        </div>
    );
}
