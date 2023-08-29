import {useState} from '@wordpress/element';
import type {ConsentProps} from '@givewp/forms/propTypes';
import Checkbox from '../Checkbox';
import ShowTerms from './ShowTerms';
import ConsentModal from './ConsentModal';
import {Markup} from 'interweave';

export default function ConsentField({
    name,
    ErrorMessage,
    fieldError,
    inputProps,
    checkboxLabel,
    displayType,
    linkText,
    linkUrl,
    modalHeading,
    modalAcceptanceText,
    agreementText,
    Label: LabelWithRequired,
}: ConsentProps) {
    const [showModal, setShowModal] = useState<boolean>(false);
    const {useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();

    const isModalDisplay = displayType === 'showModalTerms';
    const isFormDisplay = displayType === 'showFormTerms';

    const openTerms = (event) => {
        event.preventDefault();
        setShowModal(true);
    };

    const acceptTerms = (event) => {
        event.preventDefault();
        setValue(name, 'accepted');
        setShowModal(false);
    };

    const Label = () => (
        <>
            <LabelWithRequired />
            &nbsp;
            {!isFormDisplay && (
                <ShowTerms openTerms={openTerms} displayType={displayType} linkText={linkText} linkUrl={linkUrl} />
            )}
        </>
    );

    return (
        <>
            {/*// @ts-ignore*/}
            <Checkbox {...{Label, ErrorMessage, fieldError, inputProps}} value={'accepted'} />

            {isModalDisplay && showModal && (
                <ConsentModal {...{setShowModal, modalHeading, modalAcceptanceText, agreementText, acceptTerms}} />
            )}

            {isFormDisplay && (
                <div className="givewp-fields-consent__container">
                    <Markup content={agreementText} noWrap />
                </div>
            )}
        </>
    );
}
