import {FieldBlock} from '@givewp/form-builder/types';
import defaultSettings from '../settings';
import {__} from '@wordpress/i18n';
import BlockIcon from './icon';
import {Button, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {external, Icon} from '@wordpress/icons';

const login: FieldBlock = {
    name: 'givewp/login',
    settings: {
        ...defaultSettings,
        icon: BlockIcon,
        title: __('User Login', 'give'),
        description: __('Provides the donor the option to log in to complete donation', 'give'),
        supports: {
            multiple: false,
        },
        attributes: {
            required: {
                type: 'boolean',
                default: false,
            },
            loginRedirect: {
                type: 'boolean',
                default: false,
            },
            loginNotice: {
                type: 'string',
                default: __('Already have an account? Log in.', 'give'),
            },
            loginConfirmation: {
                type: 'string',
                default: __('Thank you for your continued support.', 'give'),
            },
        },
        edit: ({attributes, setAttributes}: BlockEditProps<any>) => {
            const {required, loginRedirect, loginNotice, loginConfirmation} = attributes;

            return (
                <>
                    {!!required && (
                        <div style={{display: 'flex', flexDirection: 'column', gap: '15px'}}>
                            <div style={{display: 'flex', flexDirection: 'row', gap: '15px'}}>
                                <TextControl
                                    label={__('Username', 'give')}
                                    onChange={() => null}
                                    value={''}
                                    placeholder={__('Enter your username or email', 'give')}
                                />
                                <TextControl
                                    type="password"
                                    label={__('Password', 'give')}
                                    onChange={() => null}
                                    value={''}
                                    placeholder={__('Enter your password', 'give')}
                                />
                            </div>
                            <div
                                style={{
                                    alignItems: 'center',
                                    display: 'flex',
                                    flexDirection: 'row-reverse',
                                    gap: '15px',
                                    justifyContent: 'space-between',
                                }}
                            >
                                <Button
                                    variant={'primary'}
                                    style={{
                                        backgroundColor: 'var(--givewp-neutral-70)',
                                        color: 'var(--givewp-grey-900)',
                                        borderRadius: '4px',
                                        fontSize: '1rem',
                                        fontWeight: '600',
                                        height: 'auto',
                                        lineHeight: '1.38',
                                        padding: '12px 16px',
                                    }}
                                >
                                    {__('Log In', 'give')}
                                </Button>
                                <Button
                                    style={{
                                        color: 'var(--givewp-grey-700)',
                                        display: 'inline',
                                        fontSize: '14px',
                                        fontWeight: '500',
                                        padding: '0',
                                    }}
                                >
                                    {__('Forgot your password?', 'give')} <strong>{__('Reset', 'give')}</strong>
                                </Button>
                            </div>
                        </div>
                    )}

                    {!required && (
                        <div
                            style={{
                                backgroundColor: 'var(--givewp-grey-50)',
                                borderRadius: '5px',
                                display: 'flex',
                                padding: '12px 24px',
                            }}
                        >
                            <Button
                                icon={
                                    !!loginRedirect ? (
                                        <Icon icon={external} style={{height: '20px', width: '20px'}} />
                                    ) : undefined
                                }
                                // iconPosition={'right' as 'left' | 'right'} // The icon position does not seem to be working.
                                style={{
                                    flexDirection: 'row-reverse',
                                    fontSize: '14px',
                                    height: 'auto',
                                    lineHeight: '1.5',
                                    padding: '0',
                                }}
                            >
                                {loginNotice}
                            </Button>
                        </div>
                    )}

                    <InspectorControls>
                        <PanelBody title={__('Settings', 'give')} initialOpen={true}>
                            <PanelRow>
                                <ToggleControl
                                    label={__('Require donor login', 'give')}
                                    checked={required}
                                    onChange={() => setAttributes({required: !required})}
                                    help={__(
                                        'Enable this option if you want to require the donor login by default.',
                                        'give'
                                    )}
                                />
                            </PanelRow>
                            {!required && (
                                <PanelRow>
                                    <TextControl
                                        label={__('Login Notice', 'give')}
                                        value={loginNotice}
                                        onChange={(loginNotice) => setAttributes({loginNotice})}
                                        help={__(
                                            'Add your own to customize or leave blank to use the default text placeholder.',
                                            'give'
                                        )}
                                    />
                                </PanelRow>
                            )}
                            <PanelRow>
                                <ToggleControl
                                    label={__('Redirect to login page', 'give')}
                                    checked={loginRedirect}
                                    onChange={(loginRedirect) => setAttributes({loginRedirect})}
                                />
                            </PanelRow>
                            <PanelRow>
                                <TextControl
                                    label={__('Login Confirmation', 'give')}
                                    value={loginConfirmation}
                                    onChange={(loginConfirmation) => setAttributes({loginConfirmation})}
                                    help={__(
                                        'Add your own to customize or leave blank to use the default text placeholder.',
                                        'give'
                                    )}
                                />
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                </>
            );
        },
    },
};

export default login;
