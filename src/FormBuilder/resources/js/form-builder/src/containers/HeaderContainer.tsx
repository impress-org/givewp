import React, {useState} from 'react';
import {EditIcon, GiveIcon} from '../components/icons';
import {drawerRight, moreVertical} from '@wordpress/icons';
import {setFormSettings, setTransferState, useFormState, useFormStateDispatch} from '../stores/form-state';
import {Button, Dropdown, MenuGroup, MenuItem, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {Header} from '../components';
import {Storage} from '../common';
import {FormSettings, FormStatus} from '@givewp/form-builder/types';
import {setIsDirty} from '@givewp/form-builder/stores/form-state/reducer';
import revertMissingBlocks from '@givewp/form-builder/common/revertMissingBlocks';
import {Markup} from 'interweave';
import {InfoModal, ModalType} from '../components/modal';
import {setEditorMode, useEditorState, useEditorStateDispatch} from "@givewp/form-builder/stores/editor-state";
import EditorMode from "@givewp/form-builder/types/editorMode";
import {useDispatch} from "@wordpress/data";

const Logo = () => (
    <div
        style={{
            height: '60px',
            width: '50px',
            backgroundColor: '#FFF',
            display: 'flex',
            alignItems: 'center',
        }}
    >
        <div>
            <a
                style={{display: 'block', boxShadow: 'none'}}
                href="edit.php?post_type=give_forms&page=give-forms"
                title={__('Return to GiveWP', 'give')}
            >
                <GiveIcon />
            </a>
        </div>
    </div>
);

const HeaderContainer = ({
                             SecondarySidebarButtons = null,
                             showSidebar,
                             toggleShowSidebar,
                         }) => {
    const {blocks, settings: formSettings, isDirty, transfer} = useFormState();

    const {formTitle} = formSettings;
    const dispatch = useFormStateDispatch();
    const [isSaving, setSaving] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);

    const isDraftDisabled = (isSaving || !isDirty) && 'draft' === formSettings.formStatus;
    const isPublishDisabled = (isSaving || !isDirty) && 'publish' === formSettings.formStatus;
    const {isMigratedForm, isTransferredForm} = window.migrationOnboardingData;
    const {createSuccessNotice} = useDispatch('core/notices');

    const onSave = (formStatus: FormStatus) => {
        setSaving(formStatus);

        dispatch(setFormSettings({formStatus}));

        revertMissingBlocks(blocks);

        Storage.save({blocks, formSettings: {...formSettings, formStatus}})
            .catch((error) => {
                dispatch(setIsDirty(false));
                setSaving(null);
                setErrorMessage(error.message);
            })
            .then(({formTitle, pageSlug}: FormSettings) => {
                dispatch(setFormSettings({formTitle, pageSlug}));
                dispatch(setIsDirty(false));
                setSaving(null);
                showOnSaveNotice(formStatus);
            });
    };

    const showOnSaveNotice = formStatus => {
        const notice = 'publish' === formStatus
            ? __('Form updated.', 'give')
            : __('Form published.', 'give')

        createSuccessNotice(notice, {
            type: 'snackbar',
        });
    }

    const {mode} = useEditorState();
    const dispatchEditorState = useEditorStateDispatch();
    const toggleEditorMode = () => {
        if (EditorMode.schema === mode) {
            dispatchEditorState(setEditorMode(EditorMode.design));
        }
        if (EditorMode.design === mode) {
            dispatchEditorState(setEditorMode(EditorMode.schema));
        }
    }

    // @ts-ignore
    return (
        <>
            <Header
                contentLeft={
                    <>
                        <Logo />
                        {SecondarySidebarButtons && <SecondarySidebarButtons />}
                        <Button
                            id={'editor-state-toggle'}
                            style={{backgroundColor: 'black', color: 'white', borderRadius: '4px', display: 'flex', gap: 'var(--givewp-spacing-2)', padding: 'var(--givewp-spacing-3) var(--givewp-spacing-4)'}}
                            onClick={() => toggleEditorMode()}
                            icon={EditIcon}
                        >
                            {EditorMode.schema === mode && __('Edit form design', 'give')}
                            {EditorMode.design === mode && __('Edit form', 'give')}
                        </Button>
                    </>
                }
                contentMiddle={
                    <TextControl
                        className={'givewp-form-title'}
                        value={formTitle}
                        onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                />
                }
                contentRight={
                    <>
                        <Button
                            onClick={() => onSave('draft')}
                            aria-disabled={isDraftDisabled}
                            disabled={isDraftDisabled}
                            variant="tertiary"
                        >
                            {isSaving && 'draft' === isSaving
                                ? __('Saving...', 'give')
                                : 'draft' === formSettings.formStatus
                                    ? __('Save as Draft', 'give')
                                    : __('Switch to Draft', 'give')}
                        </Button>
                        <Button
                            onClick={() => onSave('publish')}
                            aria-disabled={isPublishDisabled}
                            disabled={isPublishDisabled}
                            variant="primary"
                        >
                            {isSaving && 'publish' === isSaving
                                ? __('Updating...', 'give')
                                : 'publish' === formSettings.formStatus
                                    ? __('Update', 'give')
                                    : __('Publish', 'give')}
                        </Button>
                        <Button onClick={toggleShowSidebar} isPressed={showSidebar} icon={drawerRight} />
                        <Dropdown
                            popoverProps={{placement: 'bottom-start'}}
                            // @ts-ignore
                            focusOnMount={'container'}
                            renderToggle={({isOpen, onToggle}) => {
                                return (
                                    <Button
                                        id="FormBuilderSidebarToggle"
                                        icon={moreVertical}
                                        onClick={() => {
                                            if (transfer.showTooltip) {
                                                dispatch(setTransferState({showTooltip: false}))
                                            }
                                            onToggle();
                                        }}
                                    />
                                )
                            }}
                            renderContent={({onClose}) => (
                                <div style={{minWidth: '280px', maxWidth: '400px'}}>
                                    <MenuGroup label={__('Tools', 'give')}>
                                        <MenuItem
                                            onClick={() => {
                                                // @ts-ignore
                                                if (!window.tour.isActive()) {
                                                    // @ts-ignore
                                                    window.tour.start();
                                                    onClose();
                                                }
                                            }}
                                        >
                                            {__('Show Guided Tour', 'give')}
                                        </MenuItem>
                                        {isMigratedForm && !isTransferredForm && !transfer.showNotice && (
                                            <>
                                                <MenuItem
                                                    className={transfer.showTooltip && 'givewp-transfer-selected-menuitem'}
                                                    onClick={() => {
                                                        dispatch(setTransferState({showTransferModal: true}));
                                                        onClose();
                                                    }}
                                                >
                                                    {__('Transfer Donation Data', 'give')}
                                                </MenuItem>

                                                {transfer.showTooltip && (
                                                    <div className="givewp-transfer-tooltip">
                                                        {__('Want to transfer donation data later? Access this option in the three dots menu above at any time.', 'give')}
                                                    </div>
                                                )}
                                            </>
                                        )}
                                    </MenuGroup>
                                </div>
                            )}
                        />
                    </>
                }
            />
            {errorMessage && (
                <InfoModal
                    title={__('Error saving form', 'give')}
                    type={ModalType.Error}
                    onRequestClose={() => setErrorMessage(null)}
                    closeButtonCaption={__('Got it!', 'give')}
                >
                    <Markup content={errorMessage} />
                </InfoModal>
            )}
        </>
    );
};

export default HeaderContainer;
