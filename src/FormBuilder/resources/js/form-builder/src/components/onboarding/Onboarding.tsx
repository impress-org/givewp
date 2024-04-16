import {useContext, useEffect, useState} from 'react';
import {useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {useDispatch} from '@wordpress/data';
import {ShepherdTour, ShepherdTourContext} from 'react-shepherd';
import options from './options';
import {designSteps, schemaSteps} from './steps';
import {useEditorState} from '@givewp/form-builder/stores/editor-state';

import 'shepherd.js/dist/css/shepherd.css';
import DesignSelector from '@givewp/form-builder/components/onboarding/DesignSelector';
import SchemaWelcome from '@givewp/form-builder/components/onboarding/SchemaWelcome';

declare global {
    interface Window {
        onboardingTourData?: {
            actionUrl: string;
            autoStartDesignTour: boolean;
            autoStartSchemaTour: boolean;
        };
        migrationOnboardingData?: {
            pluginUrl: string;
            formId: number;
            apiRoot: string;
            apiNonce: string;
            migrationActionUrl: string;
            transferActionUrl: string;
            showUpgradeDialog: boolean;
            transferShowNotice: boolean;
            isMigratedForm: boolean;
            isTransferredForm: boolean;
        };
    }
}

function TourEffectsAndEvents() {
    // @ts-ignore
    const tour = window.tour = useContext(ShepherdTourContext);

    const {mode} = useEditorState();
    const dispatch = useFormStateDispatch();
    const {selectBlock} = useDispatch('core/block-editor');
    const [showToolMenu, setShowToolsMenu] = useState(!!window.onboardingTourData.autoStartSchemaTour);

    useEffect(() => {
        const selectAmountBlockCallback = () => {
            const amountBlock = document.querySelector('[data-type="givewp/donation-amount"]');
            const amountBlockId = amountBlock.getAttribute('data-block');
            selectBlock(amountBlockId).then(() => console.log('Amount block selected'));
        }

        document.addEventListener('selectAmountBlock', selectAmountBlockCallback);

        return () => {
            window.removeEventListener('selectAmountBlock', selectAmountBlockCallback);
        }
    }, [mode])

    useEffect(() => {

        const clickExitTourCallback = (event) => {
            var element = event.target as Element;
            if (tour.isActive() && element.classList.contains('js-exit-tour')) {
               const renderToolSteps = tour.steps.some(step => step.id === 'schema-find-tour');

                renderToolSteps ? tour.show('schema-find-tour') : tour.complete();
            }
        }

        document.addEventListener('click', clickExitTourCallback);

        return () => {
            window.removeEventListener('click', clickExitTourCallback);
        }
    }, [mode])

    useEffect(() => {
        const onTourComplete = () => {

            const data = new FormData();
            data.append('mode', mode);

            fetch(window.onboardingTourData.actionUrl, {
                method: 'POST',
                body: data,
            })
        }

        tour.on('complete', onTourComplete);

        return () => {
            tour.off('complete', onTourComplete);
        }
    }, [mode])

    useEffect(()=> {
        const openToolsMenuCallBack = () => {
            document.getElementById('FormBuilderSidebarToggle')?.click();
        };

        document.addEventListener('openToolsMenu', openToolsMenuCallBack);

        return () => {
            window.removeEventListener('openToolsMenu', openToolsMenuCallBack);
        };
    }, [mode]);

    return <></>;
}

const Onboarding = () => {
    const {transfer, settings: {designId}} = useFormState();
    const {mode} = useEditorState();
    const [showDesignSelector, setShowDesignSelector] = useState(!designId);
    const [showSchemaWelcome, setShowSchemaWelcome] = useState(!!window.onboardingTourData.autoStartSchemaTour);

    if (transfer.showUpgradeModal) {
        return null;
    }

    const steps = mode === 'schema' ? schemaSteps : designSteps;

    return <>
        <ShepherdTour steps={steps} tourOptions={options}>
            <TourEffectsAndEvents />
            {mode === 'design' && showDesignSelector && <DesignSelector onContinue={() => setShowDesignSelector(false)} />}
            {mode === 'schema' && showSchemaWelcome && <SchemaWelcome onContinue={() => setShowSchemaWelcome(false)} />}
        </ShepherdTour>
    </>
}

export default Onboarding;
