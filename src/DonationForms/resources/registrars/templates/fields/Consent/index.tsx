import {useState} from '@wordpress/element';
import type {ConsentProps} from '@givewp/forms/propTypes';
import Checkbox from '../Checkbox';
import ShowTerms from './ShowTerms';
import ConsentModal from './ConsentModal';
import {Interweave} from 'interweave';

export default function ConsentField({
    name,
    ErrorMessage,
    fieldError,
    inputProps,
    useGlobalSettings,
    checkboxLabel,
    displayType,
    linkText,
    linkUrl,
    modalHeading,
    modalAcceptanceText,
    agreementText,
}: ConsentProps) {
    const [showModal, setShowModal] = useState<boolean>(false);
    const [revealTerms, setRevealTerms] = useState<boolean>(false);
    const {useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();

    const isModalDisplay = displayType === 'showModalTerms';
    const isFormDisplay = displayType === 'showFormTerms';

    const openTerms = (event) => {
        event.preventDefault();

        if (isModalDisplay) {
            setShowModal(true);
        } else if (isFormDisplay) {
            setRevealTerms(!revealTerms);
        }
    };

    const acceptTerms = (event) => {
        event.preventDefault();
        setValue(name, 'accepted');
        setShowModal(false);
    };

    const Label = () => (
        <>
            <span>{checkboxLabel}</span>&nbsp;
            {(!isFormDisplay || !revealTerms) && (
                <ShowTerms openTerms={openTerms} displayType={displayType} linkText={linkText} linkUrl={linkUrl} />
            )}
        </>
    );

    return (
        <>
            <Checkbox {...{Label, ErrorMessage, fieldError, inputProps}} value={'accepted'} />

            {isModalDisplay && showModal && (
                <ConsentModal {...{setShowModal, modalHeading, modalAcceptanceText, agreementText, acceptTerms}} />
            )}
            {isFormDisplay && revealTerms && (
                <div
                    style={{
                        fontSize: '1rem',
                        lineHeight: '150%',
                        maxHeight: '16rem',
                        overflowY: 'scroll',
                        border: '1px solid #BFBFBF',
                        borderRadius: 5,
                        padding: '8px 12px',
                    }}
                >
                    <Interweave content={agreementText} />
                </div>
            )}
        </>
    );
}
