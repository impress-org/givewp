import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './AddCampaignFormModal.module.scss';
import {__} from '@wordpress/i18n';
import {useRef, useState} from 'react';

export type EditorTypeOptionProps = {
    value: string;
    label: string;
    description: string;
    editorSelected: string;
    handleEditorSelected: any;
};

/**
 * Editor Type Option component
 *
 * @unreleased
 */
const EditorTypeOption = ({value, label, description, editorSelected, handleEditorSelected}: EditorTypeOptionProps) => {
    const divRef = useRef(null);
    const labelRef = useRef(null);

    const handleDivClick = () => {
        labelRef.current.click();
    };

    return (
        <div
            className={`${styles.editorOptionsCard}  ${
                value === editorSelected ? styles.editorOptionsCardSelected : ''
            }`}
            ref={divRef}
            onClick={handleDivClick}
        >
            {value === 'visualFormBuilder' ? (
                <>
                    <img
                        src={`${window.GiveDonationForms.pluginUrl}/assets/dist/images/admin/give-settings-gateways-v3.jpg`}
                        alt={label}
                    />
                    <span>{__('Recommended', 'give')}</span>
                </>
            ) : (
                <img
                    src={`${window.GiveDonationForms.pluginUrl}/assets/dist/images/admin/give-settings-gateways-v2.jpg`}
                    alt={label}
                />
            )}
            <label ref={labelRef}>
                <input type="radio" value={value} checked={value === editorSelected} onChange={handleEditorSelected} />
                {label}
            </label>
            <p>{description}</p>
        </div>
    );
};

/**
 * Form Modal component that renders a modal with a styled form inside
 *
 * @unreleased
 */
export default function AddCampaignFormModal({isOpen, handleClose, title, campaignId}: FormModalProps) {
    const [editorSelected, setEditorSelected] = useState('');

    const handleEditorSelected = (event) => {
        setEditorSelected(event.target.value);
    };

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={styles.addFormModal}
        >
            <div className={'givewp-editor-options'}>
                <EditorTypeOption
                    value={'visualFormBuilder'}
                    label={__('Visual Form Builder', 'give')}
                    description={__(
                        'Uses the blocks-based visual form builder for creating and customizing a donation form.',
                        'give'
                    )}
                    editorSelected={editorSelected}
                    handleEditorSelected={handleEditorSelected}
                />
                <EditorTypeOption
                    value={'optionBasedFormEditor'}
                    label={__('Use Option-Based Form Editor', 'give')}
                    description={__(
                        'Uses the traditional settings options for creating and customizing a donation form.',
                        'give'
                    )}
                    editorSelected={editorSelected}
                    handleEditorSelected={handleEditorSelected}
                />
                {/*<div className={'givewp-editor-options__card'}>
                    <img
                        src={`${window.GiveDonationForms.pluginUrl}/assets/dist/images/admin/give-settings-gateways-v3.jpg`}
                        alt={__('Visual Form Builder', 'give')}
                    />
                    <span>{__('Recommended', 'give')}</span>
                    <label>{__('Visual Form Builder', 'give')}</label>
                    <p>
                        {__(
                            'Uses the blocks-based visual form builder for creating and customizing a donation form.',
                            'give'
                        )}
                    </p>
                    <a
                        href={
                            'edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=new&campaignId=' +
                            campaignId
                        }
                        className={styles.addFormButton}
                    >
                        {__('Use Visual Form Builder', 'give')}
                    </a>
                </div>
                <div className={'givewp-editor-options__card'}>
                    <img
                        src={`${window.GiveDonationForms.pluginUrl}/assets/dist/images/admin/give-settings-gateways-v2.jpg`}
                        alt={__('Option-Based Form Editor', 'give')}
                    />
                    <label>{__('Option-Based Form Editor', 'give')}</label>
                    <p>
                        {__(
                            'Uses the traditional settings options for creating and customizing a donation form.',
                            'give'
                        )}
                    </p>
                    <a
                        href={'post-new.php?post_type=give_forms&campaignId=' + campaignId}
                        className={styles.addFormButton}
                    >
                        {__('Use Option-Based Form Editor', 'give')}
                    </a>
                </div>*/}
            </div>
        </ModalDialog>
    );
}

interface FormModalProps {
    isOpen: boolean;
    handleClose: () => void;
    title: string;
    campaignId: string;
}
