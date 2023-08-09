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
            <span>{checkboxLabel}</span>&nbsp;
            {!isFormDisplay && (
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

            {isFormDisplay && (
                <div
                    style={{
                        marginTop: '1rem',
                        lineHeight: '150%',
                        maxHeight: '17.5rem',
                        minHeight: '6.5rem',
                        overflowY: 'scroll',
                        border: '1px solid var(--givewp-grey-200, #BFBFBF)',
                        borderRadius: 5,
                        padding: '.5rem 1rem',
                        background: 'var(--givewp-shades-white, #fff)',
                    }}
                >
                    <Markup content={agreementText} />
                </div>
            )}
        </>
    );
}
