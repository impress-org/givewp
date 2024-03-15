import {useContext, useState} from 'react';
import {Button, Modal} from '@wordpress/components';
import {ShepherdTourContext} from 'react-shepherd';
import {__} from '@wordpress/i18n';
import DesignCard from './components/DesignCard';

import classDesignScreenshot from './images/classic-design-screenshot.png';
import multiStepDesignScreenshot from './images/multi-step-design-screenshot.png';
import twoPanelStepsDesignScreenshot from './images/two-panel-steps-design-screenshot.png';

import {setFormSettings, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

/**
 * @since 3.6.0 Added Two-Panel design option
 * @since 3.0.0
 */
export default ({onContinue}) => {
    const tour = useContext(ShepherdTourContext);

    const dispatch = useFormStateDispatch();

    const onProceed = () => {
        onContinue();
        window.onboardingTourData.autoStartDesignTour && tour.start();
    };

    const [selectedDesign, setSelectedDesign] = useState(null);

    const onDesignSelected = (design) => {
        setSelectedDesign(design);
        dispatch(setFormSettings({designId: design}));
    };

    return (
        <Modal
            bodyOpenClassName={'show-design-selector-modal'}
            title={null}
            isDismissible={false}
            shouldCloseOnEsc={false}
            shouldCloseOnClickOutside={false}
            onRequestClose={() => null}
        >
            <div className={'givewp-design-selector--container'}>
                <header className={'givewp-design-selector--header'}>
                    <h3>{__('Choose your form layout', 'give')}</h3>
                    <p>{__('Select one that suits your taste and requirements for your cause.', 'give')}</p>
                </header>

                <div className={'givewp-design-selector--cards'}>
                    <DesignCard
                        selected={selectedDesign === 'classic'}
                        onSelected={() => onDesignSelected('classic')}
                        image={classDesignScreenshot}
                        alt={__('Classic form design', 'give')}
                        title={__('Classic', 'give')}
                        description={__(
                            'This displays all form fields on one page. Donors fill out the form as they scroll down the page',
                            'give'
                        )}
                    />
                    <DesignCard
                        selected={selectedDesign === 'multi-step'}
                        onSelected={() => onDesignSelected('multi-step')}
                        image={multiStepDesignScreenshot}
                        alt={__('Multi-Step form design', 'give')}
                        title={__('Multi-step', 'give')}
                        description={__(
                            'This walks the donor through a number of steps to the donation process. The sections are broken into steps in the form',
                            'give'
                        )}
                    />
                    <DesignCard
                        selected={selectedDesign === 'two-panel-steps'}
                        onSelected={() => onDesignSelected('two-panel-steps')}
                        image={twoPanelStepsDesignScreenshot}
                        alt={__('Two Panel form design', 'give')}
                        title={__('Two Panel', 'give')}
                        description={__(
                            'This has a side-by-side layout which breaks the sections of the donation process into steps.',
                            'give'
                        )}
                    />
                </div>

                <Button
                    disabled={!selectedDesign}
                    className={'givewp-design-selector--button'}
                    variant={'primary'}
                    onClick={onProceed}
                >
                    {__('Proceed', 'give')}
                </Button>
            </div>
        </Modal>
    );
};
