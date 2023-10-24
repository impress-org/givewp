import {Button, Modal} from '@wordpress/components';
import {edit} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import TabPanel from '@givewp/form-builder/components/sidebar/TabPanel';
import EmailTemplateSettings from './settings';
import CopyToClipboardButton from './components/copy-to-clipboard-button';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import SendPreviewEmail from './components/send-preview-email';
import EmailPreviewContent from './components/email-preview-content';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {createInterpolateElement} from '@wordpress/element';

export default () => {
    const [isOpen, setOpen] = useState<boolean>(false);
    const [showPreview, setShowPreview] = useState<boolean>(false);
    const [selectedTab, setSelectedTab] = useState<string>();

    const {
        settings: {emailTemplateOptions},
    } = useFormState();

    const {emailTemplateTags, emailNotifications, emailDefaultAddress} = getFormBuilderWindowData();

    const selectedNotificationStatus = emailTemplateOptions[selectedTab]?.status ?? 'global';

    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    const templateTagsDescription = createInterpolateElement(
        __(
            'Available template tags for this email. HTML is accepted. <a>See our documentation</a> for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
            'givewp'
        ),
        {
            a: <a href="https://make.wordpress.org" target="_blank" />,
        }
    );

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
                    title={showPreview ? __('Preview Email', 'givewp') : __('Email Settings', 'give')}
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
                                className={'email-preview__back-btn'}
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
                                <Button
                                    className={'email-settings__close-btn'}
                                    variant={'primary'}
                                    onClick={closeModal}
                                >
                                    {__('Close', 'givewp')}
                                </Button>
                                <div className={'email-settings__col-left'}>
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
                                                className={'email-settings-template'}
                                                style={{
                                                    padding:
                                                        selectedNotificationStatus === 'global'
                                                            ? '16px 20px'
                                                            : '0 20px', // Adjust for scrollbar
                                                }}
                                            >
                                                <h2 className={'email-settings__header'}>{__('Notification')}</h2>
                                                <EmailTemplateSettings notification={tab.name} />
                                            </div>
                                        )}
                                    </TabPanel>
                                </div>
                                <div
                                    className={'email-settings__col-right'}
                                    style={{
                                        visibility: 'enabled' === selectedNotificationStatus ? 'visible' : 'hidden',
                                    }}
                                >
                                    <div>
                                        <h2 className={'email-settings__header'}>{__('Preview email', 'givewp')}</h2>
                                        <p className={'email-settings__description'}>
                                            {__('Preview the email message in your browser', 'givewp')}
                                        </p>
                                        <Button
                                            className={'email-settings__email-btn'}
                                            variant={'secondary'}
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
                                        <h2 className={'email-settings__header'}>{__('Template tags', 'givewp')}</h2>
                                        <p className={'email-settings__description'}>{templateTagsDescription}</p>
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
