import type { BillingAddressProps } from "@givewp/forms/propTypes";
import { FC, useEffect, useState } from "react";
import { __ } from "@wordpress/i18n";
import { ErrorMessage } from "@hookform/error-message";
import { useCallback } from "@wordpress/element";

/**
 * @since 3.0.0
 */
type StatesJsonResponse = {
    state_label: string;
    show_field: boolean;
    states_require: boolean;
    city_require: boolean;
    zip_require: boolean;
    states_found: boolean;
    states: {[key: string]: string};
};

/**
 * @since 3.0.0
 */
type State = {
    label: string;
    value: string;
};

/**
 * Get states from the server based on the country value
 *
 * @since 3.0.0
 */
async function getStates(url, country) {
    return await fetch(url, {
        method: 'POST',
        headers: {
            'Content-type': 'application/x-www-form-urlencoded',
        },
        body: 'country=' + country,
    });
}

/**
 * This component is used to dynamically update the state field based on the country value
 *
 * @since 3.4.0 Set current state value to the state input field
 * @since 3.0.0
 */
function StateFieldContainer({
    apiUrl,
    state: HiddenStateField,
    setCityRequired,
    setZipRequired,
    nodeName,
}: {
    apiUrl: string;
    state: FC;
    setCityRequired: Function;
    setZipRequired: Function;
    nodeName: string;
}) {
    const Label = window.givewp.form.templates.layouts.fieldLabel;
    const FieldError = window.givewp.form.templates.layouts.fieldError;
    const NodeWrapper = window.givewp.form.templates.layouts.wrapper;
    const {useWatch, useFormContext, useFormState} = window.givewp.form.hooks;
    const {errors} = useFormState();
    const {setValue, clearErrors} = useFormContext();
    const country = useWatch({name: 'country'});
    const [states, setStates] = useState<State[]>([]);
    const [statesLoading, setStatesLoading] = useState<boolean>(false);
    const [stateLabel, setStateLabel] = useState<string>('State');
    const [showField, setShowField] = useState<boolean>(true);
    const [stateRequired, setStateRequired] = useState<boolean>(false);

    const updateStateValue = useCallback(
        (event) => {
            clearErrors('state');
            setValue('state', event.target.value);
        },
        [setValue, clearErrors]
    );

    const fieldError = errors?.state;

    useEffect(() => {
        if (!country) {
            setStates([]);
            return;
        }

        setStatesLoading(true);
        const response = getStates(apiUrl, country);
        response
            .then((data) => {
                if (data.ok) {
                    setStatesLoading(false);
                    setValue('state', '');

                    return data.json();
                }
                throw new Error('Fail to load states from ' + country);
            })
            .then((responseJson: StatesJsonResponse) => {
                setStateLabel(responseJson.state_label);
                setShowField(responseJson.show_field);
                setStateRequired(responseJson.states_require);
                setCityRequired(responseJson.city_require);
                setZipRequired(responseJson.zip_require);

                if (responseJson.states_found) {
                    const stateResponse: State[] = [];
                    Object.entries(responseJson.states).forEach(([key, value]) => {
                        if (key) {
                            stateResponse.push({value: key, label: value});
                        }
                    });

                    setStates(stateResponse);
                } else {
                    setStates([]);
                }
            })
            .catch((error) => {
                console.log(error);
            });
    }, [country]);

    if (!showField) {
        return <HiddenStateField />;
    }

    if (states.length > 0) {
        return (
            /**
             * TODO: replace with template api component
             */
            <NodeWrapper nodeType="fields" type="select" htmlTag="div" name="state">
                <label>
                    <Label label={stateLabel} required={stateRequired} />

                    <select
                        onChange={updateStateValue}
                        disabled={statesLoading}
                        aria-invalid={fieldError ? 'true' : 'false'}
                    >
                        {statesLoading ? (
                            <>
                                <option hidden>{__('Loading...', 'give')}</option>
                                <option disabled>{__('Loading...', 'give')}</option>
                            </>
                        ) : (
                            <>
                                <option hidden>{__(`Select ${stateLabel}`, 'give')}</option>
                                <option disabled>{__(`Select ${stateLabel}`, 'give')}</option>
                            </>
                        )}
                        {states.map(({label, value}) => (
                            <option key={value} value={value}>
                                {label ?? value}
                            </option>
                        ))}
                    </select>

                    <HiddenStateField />

                    <ErrorMessage
                        errors={errors}
                        name={'state'}
                        render={({message}) => <FieldError error={message} name={nodeName} />}
                    />
                </label>
            </NodeWrapper>
        );
    }

    return (
        /**
         * TODO: replace with template api component
         */
        <NodeWrapper nodeType="fields" type="text" htmlTag="div" name="state">
            <label>
                <Label label={stateLabel ?? __('State', 'give')} required={stateRequired} />

                <input
                    type="text"
                    onChange={updateStateValue}
                    aria-invalid={fieldError ? 'true' : 'false'}
                    placeholder={statesLoading ? __('Loading...', 'give') : ''}
                    disabled={statesLoading}
                />

                <HiddenStateField />

                <ErrorMessage
                    errors={errors}
                    name="state"
                    render={({message}) => <FieldError error={message} name={nodeName} />}
                />
            </label>
        </NodeWrapper>
    );
}

/**
 * @since 3.4.0 Update city and zip components before rendering to display required asterisk
 * @since 3.0.0
 */
export default function BillingAddress({
    groupLabel,
    nodeComponents: {country: Country, address1: Address1, address2: Address2, city: City, state, zip: Zip},
    apiUrl,
    name,
}: BillingAddressProps) {
    // these are necessary to set the required indicator on the city and zip field labels
    // the actual validation will come from the server as we don't yet have the ability to update the actual client validation rules here
    const [cityRequired, setCityRequired] = useState(false);
    const [zipRequired, setZipRequired] = useState(false);

    const CityWithRequired = () => <City validationRules={{required: cityRequired}} />
    const ZipWithRequired = () => <Zip validationRules={{required: zipRequired}} />

    return (
        <>
            <fieldset>
                {groupLabel && <legend>{groupLabel}</legend>}
                <Country />
                <Address1 />
                <Address2 />
                <CityWithRequired />
                <StateFieldContainer
                    apiUrl={apiUrl}
                    state={state}
                    setCityRequired={setCityRequired}
                    setZipRequired={setZipRequired}
                    nodeName={name}
                />
                <ZipWithRequired />
            </fieldset>
        </>
    );
}
