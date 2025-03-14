import React, {useState} from 'react';
import {CodeIcon, GiveIcon} from '../components/icons';
import {drawerRight, external, moreVertical} from '@wordpress/icons';
import {setFormSettings, setTransferState, useFormState, useFormStateDispatch} from '../stores/form-state';
import {Button, Dropdown, ExternalLink, MenuGroup, MenuItem, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {Header} from '../components';
import {getWindowData, Storage} from '../common';
import {FormSettings, FormStatus} from '@givewp/form-builder/types';
import {setIsDirty} from '@givewp/form-builder/stores/form-state/reducer';
import revertMissingBlocks from '@givewp/form-builder/common/revertMissingBlocks';
import {Markup} from 'interweave';
import {InfoModal, ModalType} from '../components/modal';
import FormPrepublishPanel from '@givewp/form-builder/components/sidebar/panels/FormPrepublishPanel';
import {setEditorMode, useEditorState, useEditorStateDispatch} from '@givewp/form-builder/stores/editor-state';
import EditorMode from '@givewp/form-builder/types/editorMode';
import {useDispatch} from '@wordpress/data';
import {cleanForSlug} from '@wordpress/url';
import cn from 'classnames';
import EmbedFormModal from '@givewp/form-builder/components/EmbedForm';

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
        <a
            style={{display: 'flex', boxShadow: 'none'}}
            href="edit.php?post_type=give_forms&page=give-forms"
            title={__('Return to GiveWP', 'give')}
        >
            <GiveIcon />
        </a>
    </div>
);

/**
 * @since 3.1.0 dispatch page slug from form title on initial publish.
 */
const HeaderContainer = ({SecondarySidebarButtons = null, showSidebar, toggleShowSidebar}) => {
    const {blocks, settings: formSettings, isDirty, transfer} = useFormState();

    const {formTitle} = formSettings;
    const dispatch = useFormStateDispatch();
    const [isSaving, setSaving] = useState(null);
    const [errorMessage, setErrorMessage] = useState(null);
    const [showEmbedModal, setShowEmbedModal] = useState(false);
    const [showPublishConfirmation, setShowPublishConfirmation] = useState(false);

    const isDraftDisabled = (isSaving || !isDirty) && 'draft' === formSettings.formStatus;
    const isPublishDisabled = (isSaving || !isDirty) && ['publish', 'private'].includes(formSettings.formStatus);
    const isPublished = ['publish', 'private'].includes(formSettings.formStatus);
    const {isMigratedForm, isTransferredForm} = window.migrationOnboardingData;
    const {createSuccessNotice} = useDispatch('core/notices');

    const {
        formPage: {permalink},
    } = getWindowData();

    const onSave = (formStatus: FormStatus) => {
        setSaving(formStatus);

        const status = 'draft' === formStatus ? formStatus : formSettings.newFormStatus ?? formStatus;

        dispatch(setFormSettings({formStatus: status, newFormStatus: null}));

        revertMissingBlocks(blocks);

        Storage.save({blocks, formSettings: {...formSettings, formStatus: status}})
            .catch((error) => {
                dispatch(setIsDirty(false));
                setSaving(null);
                setErrorMessage(error.message);
                setShowPublishConfirmation(false);
            })
            .then(({formTitle, pageSlug}: FormSettings) => {
                dispatch(setFormSettings({formTitle, pageSlug}));
                dispatch(setIsDirty(false));
                setSaving(null);
                showOnSaveNotice(formStatus);
            });
    };

    const showOnSaveNotice = (formStatus: string) => {
        if ('draft' === formStatus) {
            createSuccessNotice(__('Draft saved.', 'give'), {
                type: 'snackbar',
            });
        } else {
            const notice =
                'publish' === formStatus && formSettings.formStatus !== 'draft'
                    ? __('Form updated.', 'give')
                    : __('Form published.', 'give');

            createSuccessNotice(notice, {
                type: 'snackbar',
                actions: [
                    {
                        label: __('View form', 'give'),
                        url: permalink,
                    },
                ],
            });
        }
    }

    const {mode} = useEditorState();
    const dispatchEditorState = useEditorStateDispatch();
    const switchEditorMode = (newMode: EditorMode) => {
        if (newMode && Object.keys(EditorMode).includes(newMode)) {
            dispatchEditorState(setEditorMode(newMode));
        }
    };

    // @ts-ignore
    return (
        <>
            <Header
                contentLeft={
                    <>
                        <Logo />
                        {SecondarySidebarButtons && <SecondarySidebarButtons />}
                        <TextControl
                            className={'givewp-form-title'}
                            value={formTitle}
                            onChange={(formTitle) => {
                                !isPublished && dispatch(setFormSettings({pageSlug: cleanForSlug(formTitle)}));
                                dispatch(setFormSettings({formTitle}));
                            }}
                            label={
                                <>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 16 16"
                                        fill="none"
                                    >
                                        <path
                                            d="M6.02157 10.1075L12.666 3.39785L11.2216 2L4.57713 8.70968L3.99935 10.6667L6.02157 10.1075Z"
                                            fill="currentColor"
                                        />
                                        <path
                                            d="M2.66602 13.3333H7.99935M12.666 3.39785L6.02157 10.1075L3.99935 10.6667L4.57713 8.70968L11.2216 2L12.666 3.39785Z"
                                            stroke="currentColor"
                                        />
                                    </svg>
                                </>
                            }
                        />
                    </>
                }
                contentMiddle={
                    <div className={'givewp-header-tabs'}>
                        <Button
                            id={'editor-state-switch-schema'}
                            className={cn('givewp-header-tabs__tab', {
                                'is-active': mode === EditorMode.schema,
                            })}
                            onClick={() => switchEditorMode(EditorMode.schema)}
                        >
                            {__('Build', 'give')}
                        </Button>
                        <Button
                            id={'editor-state-switch-design'}
                            className={cn('givewp-header-tabs__tab', {
                                'is-active': mode === EditorMode.design,
                            })}
                            onClick={() => switchEditorMode(EditorMode.design)}
                        >
                            {__('Design', 'give')}
                        </Button>
                        <Button
                            id={'editor-state-switch-settings'}
                            className={cn('givewp-header-tabs__tab', {
                                'is-active': mode === EditorMode.settings,
                            })}
                            onClick={() => switchEditorMode(EditorMode.settings)}
                        >
                            {__('Settings', 'give')}
                        </Button>
                    </div>
                }
                contentRight={
                    <>
                        {!showPublishConfirmation && (
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
                                    icon={CodeIcon}
                                    className="givewp-embed-button"
                                    isPressed={showEmbedModal}
                                    onClick={() => setShowEmbedModal(!showEmbedModal)}
                                    label={__('Embed form', 'give')}
                                    title={__('Embed form', 'give')}
                                />
                                {isPublished && (
                                    <Button
                                        label={__('View form', 'give')}
                                        title={__('View form', 'give')}
                                        href={permalink}
                                        target="_blank"
                                        icon={external}
                                    />
                                )}
                                <Button
                                    onClick={() => (isPublished ? onSave('publish') : setShowPublishConfirmation(true))}
                                    aria-disabled={isPublishDisabled}
                                    disabled={isPublishDisabled}
                                    variant="primary"
                                >
                                    {isSaving && 'publish' === isSaving
                                        ? __('Updating...', 'give')
                                        : isPublished
                                        ? __('Update', 'give')
                                        : __('Publish', 'give')}
                                </Button>
                                {mode !== EditorMode.settings && (
                                    <Button
                                        onClick={toggleShowSidebar}
                                        isPressed={showSidebar}
                                        icon={drawerRight}
                                        label={__('Settings', 'give')}
                                        title={__('Settings', 'give')}
                                    />
                                )}
                            </>
                        )}
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
                                                dispatch(setTransferState({showTooltip: false}));
                                            }
                                            onToggle();
                                        }}
                                        label={__('Options', 'give')}
                                        title={__('Options', 'give')}
                                    />
                                );
                            }}
                            renderContent={({onClose}) => (
                                <div style={{minWidth: '280px', maxWidth: '400px'}}>
                                    <MenuGroup label={__('Support', 'give')}>
                                        {mode !== EditorMode.settings && (
                                            <MenuItem
                                                className={'givewp-block-editor-tools__tour'}
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
                                        )}
                                        {isMigratedForm && !isTransferredForm && !transfer.showNotice && (
                                            <>
                                                <MenuItem
                                                    className={
                                                        transfer.showTooltip && 'givewp-transfer-selected-menuitem'
                                                    }
                                                    onClick={() => {
                                                        dispatch(setTransferState({showTransferModal: true}));
                                                        onClose();
                                                    }}
                                                >
                                                    {__('Transfer Donation Data', 'give')}
                                                </MenuItem>

                                                {transfer.showTooltip && (
                                                    <div className="givewp-transfer-tooltip">
                                                        {__(
                                                            'Want to transfer donation data later? Access this option in the three dots menu above at any time.',
                                                            'give'
                                                        )}
                                                    </div>
                                                )}
                                            </>
                                        )}
                                    </MenuGroup>
                                    <ExternalLink
                                        className="givewp-support-link"
                                        href="https://docs.givewp.com/nextgenfeedback"
                                        rel="noopener noreferrer"
                                    >
                                        <MenuItem icon={external}>{__('Submit Feedback', 'give')}</MenuItem>
                                    </ExternalLink>
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
            {showEmbedModal && <EmbedFormModal handleClose={() => setShowEmbedModal(false)} />}
            {showPublishConfirmation && (
                <FormPrepublishPanel
                    isSaving={isSaving}
                    isPublished={isPublished}
                    handleSave={() => onSave('publish')}
                    handleClose={() => setShowPublishConfirmation(false)}
                />
            )}
        </>
    );
};

export default HeaderContainer;
