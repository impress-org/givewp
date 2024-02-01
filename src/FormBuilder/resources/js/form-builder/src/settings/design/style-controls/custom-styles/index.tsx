import {useState} from 'react';
import AceEditor from 'react-ace';
import debounce from 'lodash.debounce';
import {__} from '@wordpress/i18n';
import {fullscreen} from '@wordpress/icons';
import {Button, Modal, PanelBody, PanelRow} from '@wordpress/components';

import 'ace-builds/src-noconflict/mode-css';
import 'ace-builds/src-noconflict/snippets/css';

import 'ace-builds/src-noconflict/theme-textmate';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

const CustomStyles = () => {
    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    return (
        <PanelBody title={__('Custom Styles', 'give')} initialOpen={false}>
            <PanelRow>
                <div
                    style={{
                        width: '100%',
                        display: 'flex',
                        flexDirection: 'column',
                        gap: '10px',
                    }}
                >
                    <Button icon={fullscreen} onClick={openModal}>
                        {__('Edit in Modal', 'give')}
                    </Button>

                    <div
                        style={{
                            margin: '.5rem -16px -16px -16px', // Offset the panel padding in order to fill the inspector width.
                        }}
                    >
                        <CustomStyleCodeControl />
                    </div>
                </div>

                {!!isOpen && (
                    <Modal
                        overlayClassName="components-modal__screen-overlay--givewpwp-custom-css"
                        title={__('Custom Styles', 'give')}
                        onRequestClose={closeModal}
                        style={{
                            height: '100%',
                            maxHeight: '100%', // Override the max height of the modal component.
                            width: '500px',
                            position: 'absolute',
                            right: '0',
                        }}
                    >
                        <div
                            style={{
                                margin: '0 -31px -23px -31px', // Offset the modal padding in order to fill the available space.
                            }}
                        >
                            <CustomStyleCodeControl />
                        </div>
                    </Modal>
                )}
            </PanelRow>
        </PanelBody>
    );
};

const CustomStyleCodeControl = () => {
    const {
        settings: {customCss},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const {publishCss} = useDonationFormPubSub();

    return (
        <AceEditor
            mode="css"
            theme="textmate"
            onLoad={(editor) => {
                editor.renderer.setScrollMargin(8, 8, 8, 8);
                editor.renderer.setPadding(8);
            }}
            onChange={debounce((customCss) => {
                dispatch(setFormSettings({customCss}));
                publishCss({customCss});
            }, 500)}
            showPrintMargin={false}
            highlightActiveLine={false}
            showGutter={true}
            value={customCss}
            maxLines={Infinity}
            minLines={5}
            width="100%"
            setOptions={{
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true,
                enableSnippets: true,
                showLineNumbers: true,
                tabSize: 2,
                useWorker: false,
            }}
        />
    );
};

export default CustomStyles;
