import {Button, Modal} from "@wordpress/components";
import {edit} from "@wordpress/icons";
import {__} from "@wordpress/i18n";
import {useState} from "react";
import TabPanel from "@givewp/form-builder/components/sidebar/TabPanel";
import EmailTemplateSettings from "./settings";
import CopyToClipboardButton from "./components/copy-to-clipboard-button";
import {getFormBuilderData} from "@givewp/form-builder/common/getWindowData";
import SendPreviewEmail from "./components/send-preview-email";
import EmailPreviewContent   from "./components/email-preview-content";
import {useFormState} from "@givewp/form-builder/stores/form-state";

export default () => {

    const [ isOpen, setOpen ] = useState<boolean>( false );
    const openModal = () => setOpen( true );
    const closeModal = () => setOpen( false );

    const [ selectedTab, setSelectedTab ] = useState<string>();
    const {settings: {emailTemplateOptions}} = useFormState();
    const selectedNotificationStatus = emailTemplateOptions[selectedTab]?.status ?? 'global';

    const [ showPreview, setShowPreview ] = useState<boolean>( false );

    const {emailTemplateTags, emailNotifications} = getFormBuilderData();

    const CloseButton = ({label, onClick}) => {
        return (
            <Button
                variant={'primary'}
                onClick={onClick}
                style={{
                    zIndex: 11, // Above the modal header
                    position: 'absolute',
                    top: 0,
                    right: 0,
                    padding: 'var(--givewp-spacing-4) var(--givewp-spacing-6)',
                    margin: 'var(--givewp-spacing-4) var(--givewp-spacing-6)',
                }}
            >
                {label}
            </Button>
        )
    }

    return (
        <>
            <Button icon={edit} onClick={ openModal } variant={'secondary'} style={{width:'100%', justifyContent:'center'}}>
                { __( 'Customize email templates', 'givewp' ) }
            </Button>
            {isOpen && (
                <Modal
                    title={
                        showPreview
                            ? __('Preview Email', 'give')
                            : __('Email Settings', 'give')
                    }
                    onRequestClose={closeModal}
                    isDismissible={false}
                    shouldCloseOnClickOutside={false}
                    style={{
                        margin: 'var(--givewp-spacing-10)',
                        width: '100%',
                        height: '90%',
                        maxHeight: '90%',
                        display: 'flex',
                    }}
                >
                    {showPreview && (
                        <>
                            <EmailPreviewContent
                                emailType={selectedTab}
                            />
                            <CloseButton
                                label={__('Back to email settings', 'givewp')}
                                onClick={() => setShowPreview(false)} />
                        </>
                    )}

                    {!showPreview && (
                        <>
                            <CloseButton label={__('Set and close', 'givewp')} onClick={closeModal} />
                            {/* Note: I tried extracting these to a wrapper component, but that broken focus due to re-renders. */}
                            <div style={{ display: 'flex', gap: 'var(--givewp-spacing-10)', height: '100%', overflow: 'hidden', flex: 1}}>
                                <div style={{ flex: '2'}}>
                                    <TabPanel
                                        className={'email-settings-modal-tabs'}
                                        orientation={'vertical' as 'horizontal' | 'vertical' | 'both'}
                                        tabs={emailNotifications.map((notification) => {
                                            return {
                                                name: notification.id,
                                                title: notification.title,
                                            }
                                        })}
                                        initialTabName={emailNotifications[0].id}
                                        state={[selectedTab, setSelectedTab]}
                                    >
                                        {(tab) => (
                                            <div style={{
                                                height: '100%',
                                                overflowX: 'hidden',
                                                overflowY: 'scroll',
                                                padding: '20px', // Adjust for scrollbar
                                            }}>
                                                <h2 style={{margin:0}}>Notification</h2>
                                                <p></p>
                                                <EmailTemplateSettings notification={tab.name} />
                                            </div>
                                        )}
                                    </TabPanel>
                                </div>
                                <div style={{
                                    flex: '1',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: 'var(--givewp-spacing-6)',
                                    paddingRight: '10px', // Adjust for overflow
                                    visibility: 'enabled' === selectedNotificationStatus ? 'visible' : 'hidden',
                                }}>
                                    <div style={{flex:1}}>
                                        <h2 style={{margin: 0}}>{__('Preview email', 'givewp')}</h2>
                                        <p>{__('Specify below the email address you want to send a test email to', 'givewp')}</p>
                                        <Button
                                            variant={'secondary'}
                                            style={{width:'100%',justifyContent:'center'}}
                                            onClick={() => setShowPreview(true)}
                                        >
                                            {__('Preview email', 'givewp')}
                                        </Button>
                                    </div>
                                    <div style={{flex:1}}>
                                        <SendPreviewEmail emailType={selectedTab} />
                                    </div>
                                    <div style={{flex:3}}>
                                        <h2 style={{margin: 0}}>{__('Template tags', 'givewp')}</h2>
                                        <p>{__('Available template tags for this email. HTML is accepted. See our documentation for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails', 'givewp')}</p>
                                        <ul className={'email-template-tags'}>
                                            {emailTemplateTags.map((tag) => (
                                                <li key={tag.tag}>
                                                    <strong>{'{' + tag.tag + '}'}</strong>
                                                    <p>{tag.desc}</p>
                                                    <CopyToClipboardButton text={'{' + tag.tag + '}'} />
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}

                </Modal>
            )}
        </>
    )
}
