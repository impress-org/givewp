import {Button, Modal} from '@wordpress/components';
import {edit} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import TabPanel from '@givewp/form-builder/components/sidebar/TabPanel';
import EmailTemplateSettings from './settings';
import CopyToClipboardButton from './components/copy-to-clipboard-button';
import {getFormBuilderData} from '@givewp/form-builder/common/getWindowData';
import SendPreviewEmail from './components/send-preview-email';
import EmailPreviewContent from './components/email-preview-content';
import {useFormState} from '@givewp/form-builder/stores/form-state';

export default () => {
    const [isOpen, setOpen] = useState<boolean>(false);
    const [showPreview, setShowPreview] = useState<boolean>(false);
    const [selectedTab, setSelectedTab] = useState<string>();

    const {
        settings: {emailTemplateOptions},
    } = useFormState();

    const {emailTemplateTags, emailNotifications, emailDefaultAddress} = getFormBuilderData();

    const selectedNotificationStatus = emailTemplateOptions[selectedTab]?.status ?? 'global';

    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    return (
        <>
            <Button
                icon={edit}
                onClick={openModal}
                variant={'secondary'}
                style={{width: '100%', justifyContent: 'center'}}
            >
                {__('Customize email templates', 'givewp')}
            </Button>
            {isOpen && (
                <Modal
                    title={showPreview ? __('Preview Email', 'give') : __('Email Settings', 'give')}
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
                            <EmailPreviewContent emailType={selectedTab} />
                            <Button
                                style={{
                                    zIndex: 11, // Above the modal header
                                    position: 'absolute',
                                    top: 0,
                                    right: 0,
                                    margin: 'var(--givewp-spacing-5) var(--givewp-spacing-8)',
                                    padding: 'var(--givewp-spacing-4) var(--givewp-spacing-8)',
                                }}
                                variant={'secondary'}
                                onClick={() => setShowPreview(false)}
                            >
                                {__('Back to template settings', 'givewp')}
                            </Button>
                        </>
                    )}

                    {!showPreview && (
                        <>
                            <div className={'email-settings'}>
                                <div className={'email-settings-col-left'}>
                                    <TabPanel
                                        className={'email-settings-modal-tabs'}
                                        orientation={'vertical' as 'horizontal' | 'vertical' | 'both'}
                                        tabs={emailNotifications.map((notification) => {
                                            return {
                                                name: notification.id,
                                                title: notification.title,
                                            };
                                        })}
                                        initialTabName={emailNotifications[0].id}
                                        state={[selectedTab, setSelectedTab]}
                                    >
                                        {(tab) => (
                                            <div
                                                className={'email-settings-template-wrapper'}
                                                style={{
                                                    padding:
                                                        selectedNotificationStatus === 'global'
                                                            ? '16px 20px'
                                                            : '0 20px', // Adjust for scrollbar
                                                }}
                                            >
                                                <h2 className={'email-settings-header'}>{__('Notification')}</h2>
                                                <EmailTemplateSettings
                                                    closeModal={closeModal}
                                                    notification={tab.name}
                                                />
                                            </div>
                                        )}
                                    </TabPanel>
                                </div>
                                <div
                                    className={'email-settings-col-right'}
                                    style={{
                                        visibility: 'enabled' === selectedNotificationStatus ? 'visible' : 'hidden',
                                    }}
                                >
                                    <div>
                                        <h2 className={'email-settings-header'}>{__('Preview email', 'givewp')}</h2>
                                        <p className={'email-settings-description'}>
                                            {__('Preview the email message in your browser', 'givewp')}
                                        </p>
                                        <Button
                                            variant={'secondary'}
                                            style={{width: '100%', justifyContent: 'center'}}
                                            onClick={() => setShowPreview(true)}
                                        >
                                            {__('Preview email', 'givewp')}
                                        </Button>
                                    </div>
                                    <div>
                                        <SendPreviewEmail
                                            defaultEmailAddress={emailDefaultAddress}
                                            emailType={selectedTab}
                                        />
                                    </div>
                                    <div>
                                        <h2 className={'email-settings-header'}>{__('Template tags', 'givewp')}</h2>
                                        <p className={'email-settings-description'}>
                                            {__(
                                                'Available template tags for this email. HTML is accepted. See our documentation for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
                                                'givewp'
                                            )}
                                        </p>
                                        <ul className={'email-settings-template-tags'}>
                                            {emailTemplateTags.map((tag) => (
                                                <li key={tag.tag}>
                                                    <strong>{'{' + tag.tag + '}'}</strong>
                                                    <p style={{fontSize: '.75rem'}}>{tag.desc}</p>
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
    );
};
