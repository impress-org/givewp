import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './AddCampaignFormModal.module.scss';
import {__} from '@wordpress/i18n';
import {useRef, useState} from 'react';

export type EditorTypeOptionProps = {
    editorType: string;
    label: string;
    description: string;
    editorSelected: string;
    handleEditorSelected: any;
};

const EditorSelectedIcon = () => {
    return (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M12 1C5.925 1 1 5.925 1 12s4.925 11 11 11 11-4.925 11-11S18.075 1 12 1zm5.207 8.707a1 1 0 0 0-1.414-1.414L10.5 13.586l-2.293-2.293a1 1 0 0 0-1.414 1.414l3 3a1 1 0 0 0 1.414 0l6-6z"
                fill="#459948"
            />
        </svg>
    );
};

/**
 * Editor Type Option component
 *
 * @unreleased
 */
const EditorTypeOption = ({
    editorType,
    label,
    description,
    editorSelected,
    handleEditorSelected,
}: EditorTypeOptionProps) => {
    const divRef = useRef(null);
    const labelRef = useRef(null);

    const handleDivClick = () => {
        labelRef.current.click();
    };

    return (
        <div
            className={`givewp-editor-options__option  ${
                editorType === editorSelected ? 'givewp-editor-options__option_selected' : ''
            }`}
            ref={divRef}
            onClick={handleDivClick}
        >
            <img
                src={`${window.GiveDonationForms.pluginUrl}${
                    editorType === 'visualFormBuilder'
                        ? '/assets/dist/images/admin/give-settings-gateways-v3.jpg'
                        : '/assets/dist/images/admin/give-settings-gateways-v2.jpg'
                }`}
                alt={label}
            />
            {editorType === 'visualFormBuilder' && (
                <span className={'givewp-editor-options__option_recommended'}>{__('Recommended', 'give')}</span>
            )}
            <label ref={labelRef}>
                <input
                    type="radio"
                    value={editorType}
                    checked={editorType === editorSelected}
                    onChange={handleEditorSelected}
                />
                {label}
            </label>
            <p>{description}</p>
            <div className={'givewp-editor-options__option_selected_icon'}>
                <EditorSelectedIcon />
            </div>
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
            <>
                <div className={'givewp-editor-options'}>
                    <EditorTypeOption
                        editorType={'visualFormBuilder'}
                        label={__('Visual Form Builder', 'give')}
                        description={__(
                            'Uses the blocks-based visual form builder for creating and customizing a donation form.',
                            'give'
                        )}
                        editorSelected={editorSelected}
                        handleEditorSelected={handleEditorSelected}
                    />
                    <EditorTypeOption
                        editorType={'optionBasedFormEditor'}
                        label={__('Use Option-Based Form Editor', 'give')}
                        description={__(
                            'Uses the traditional settings options for creating and customizing a donation form.',
                            'give'
                        )}
                        editorSelected={editorSelected}
                        handleEditorSelected={handleEditorSelected}
                    />
                </div>
                <div className={'givewp-editor-actions'}>
                    <a
                        href={
                            editorSelected === 'visualFormBuilder'
                                ? `edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=new&locale=${window.GiveDonationForms.locale}&campaignId=${campaignId}`
                                : `post-new.php?post_type=give_forms&campaignId=${campaignId}`
                        }
                        className={`button button-primary givewp-editor-actions__button ${
                            !editorSelected ? 'disabled' : ''
                        }`}
                    >
                        {__('Proceed', 'give')}
                    </a>
                </div>
            </>
        </ModalDialog>
    );
}

interface FormModalProps {
    isOpen: boolean;
    handleClose: () => void;
    title: string;
    campaignId: string;
}
