import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import ReactSelect from 'react-select';
import logo from './givewp-logo.svg';
import usePostState from "../../hooks/usePostState";
import {reactSelectStyles, reactSelectThemeStyles} from "./styles/reactSelectStyles";
import './styles/index.scss';

/**
 * @since 4.3.0
 */
export type EntityOption = {
    label: string;
    value: number | string;
};

/**
 * @since 4.3.0
 */
type EntitySelectorProps = {
    id: string;
    label: string;
    options: EntityOption[];
    isLoading: boolean;
    emptyMessage: string;
    loadingMessage: string;
    onConfirm: (id: number | string) => void;
    buttonText?: string;
    disabled?: boolean;
};

// @ts-ignore
const savePost = () => dispatch('core/editor').savePost();

/**
 * @since 4.3.0
 */
export default function EntitySelector({
    id,
    label,
    options,
    isLoading,
    emptyMessage,
    loadingMessage,
    onConfirm,
    buttonText = __('Confirm', 'give'),
    disabled = false,
}: EntitySelectorProps) {
    const [selected, setSelected] = useState<number | string | null>(null);
    const selectedOption = options.find((opt) => opt.value === selected);
    const {isSaving, isDisabled} = usePostState();

    return (
        <div className="givewp-entity-selector">
            <img className="givewp-entity-selector__logo" src={logo} alt="givewp-logo" />
            <div className="givewp-entity-selector__select">
                <label htmlFor={id} className="givewp-entity-selector__label">
                    {label}
                </label>

                <ReactSelect
                    name={id}
                    inputId={id}
                    value={selectedOption}
                    //@ts-ignore
                    onChange={(option) => setSelected(option?.value)}
                    options={options}
                    noOptionsMessage={() => <p>{emptyMessage}</p>}
                    loadingMessage={() => <>{loadingMessage}</>}
                    isLoading={isLoading}
                    theme={reactSelectThemeStyles}
                    styles={reactSelectStyles}
                    placeholder={isLoading ? loadingMessage : __('Select...', 'give')}
                />
            </div>

            <button
                className="givewp-entity-selector__submit"
                type="button"
                disabled={isSaving || isDisabled || !selected}
                onClick={() => {
                    if (selected) {
                        onConfirm(selected);
                    }
                    savePost();
                }}
            >
                {buttonText}
            </button>
        </div>
    );
}
