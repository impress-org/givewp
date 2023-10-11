import {FieldBlock} from '@givewp/form-builder/types';
import defaultSettings from '../settings';
import {__} from "@wordpress/i18n";
import BlockIcon from "./icon";
import {Button, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {BlockEditProps} from "@wordpress/blocks";
import {Icon, external} from "@wordpress/icons";

const login: FieldBlock = {
    name: 'givewp/login',
    settings: {
        ...defaultSettings,
        icon: BlockIcon,
        title: __('User Login', 'give'),
        description: __('...', 'give'),
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
                default: __('Already have an account? Log in.', 'givewp'),
            },
            loginConfirmation: {
                type: 'string',
                default: __('Thank you for your continued support.', 'give'),
            }
        },
        edit: ({attributes, setAttributes}: BlockEditProps<any>) => {
            const {required, loginRedirect, loginNotice, loginConfirmation} = attributes;

            return (
                <>

                    {!!required && (
                        <div style={{display: 'flex', flexDirection: 'column', gap: '15px'}}>
                            <div style={{display: 'flex', flexDirection: 'row', gap: '15px'}}>
                                <TextControl
                                    label={__('Login', 'givewp')}
                                    onChange={() => null}
                                    value={''}
                                    placeholder={__('Username or Email Address', 'givewp')}
                                />
                                <TextControl
                                    type="password"
                                    label={__('Password', 'givewp')}
                                    onChange={() => null}
                                    value={'password123'}
                                />
                            </div>
                            <div style={{display: 'flex', flexDirection: 'row-reverse', gap: '15px', justifyContent: 'space-between'}}>
                                <Button variant={'primary'}>{__('Log In', 'givewp')}</Button>
                                <Button variant={'link'}>{__('Reset Password', 'givewp')}</Button>
                            </div>
                        </div>
                    )}

                    {!required && (
                        <div style={{display: 'flex', flexDirection: "row-reverse"}}>
                            <Button
                                variant={'link'}
                                icon={!!loginRedirect ? <Icon icon={external} /> : undefined}
                                // iconPosition={'right' as 'left' | 'right'} // The icon position does not seem to be working.
                                style={{flexDirection: 'row-reverse'}}
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
                                />
                            </PanelRow>
                            <PanelRow>
                                <ToggleControl
                                    label={__('Redirect to login page', 'give')}
                                    checked={loginRedirect}
                                    onChange={(loginRedirect) => setAttributes({loginRedirect})}
                                />
                            </PanelRow>
                            <PanelRow>
                                <TextControl
                                    label={__('Login Notice', 'give')}
                                    value={loginNotice}
                                    onChange={(loginNotice) => setAttributes({loginNotice})}
                                />
                            </PanelRow>
                            <PanelRow>
                                <TextControl
                                    label={__('Login Confirmation', 'give')}
                                    value={loginConfirmation}
                                    onChange={(loginConfirmation) => setAttributes({loginConfirmation})}
                                />
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                </>
            )
        },
    }
};

export default login;
