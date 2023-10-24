import {useContext} from 'react';
import {__} from '@wordpress/i18n';
import {ShepherdTourContext} from 'react-shepherd';
import {Button, Modal} from '@wordpress/components';
import Logo from '@givewp/form-builder/components/icons/logo';

export default ({onContinue}) => {
    const tour = useContext(ShepherdTourContext);

    const onProceed = () => {
        onContinue();
        tour.start();
    };

    return (
        <Modal
            bodyOpenClassName={'show-schema-welcome-modal'}
            title={null}
            isDismissible={false}
            shouldCloseOnEsc={false}
            shouldCloseOnClickOutside={false}
            onRequestClose={() => null}
        >
            <div className={'givewp-schema-welcome--container'}>
                <div>
                    <div style={{display: 'flex', justifyContent: 'center', margin: '0 auto var(--givewp-spacing-4)'}}>
                        <Logo />
                    </div>

                    <h3>{__('Learn how to edit your form', 'give')}</h3>

                    <p>
                        {__(
                            'Let’s quickly explore the new visual donation form builder. This will take less than a minute.',
                            'give'
                        )}
                    </p>
                </div>

                <Button className={'givewp-schema-welcome--button'} variant={'primary'} onClick={onProceed}>
                    {__('Get started', 'give')}
                </Button>
            </div>
        </Modal>
    );
};
