import {useContext, useEffect, useState} from 'react';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {useDispatch} from '@wordpress/data';
import {ShepherdTour, ShepherdTourContext} from 'react-shepherd';
import options from './options';
import {designSteps, schemaSteps} from './steps';

import 'shepherd.js/dist/css/shepherd.css';
import DesignSelector from "@givewp/form-builder/components/onboarding/DesignSelector";
import SchemaWelcome from "@givewp/form-builder/components/onboarding/SchemaWelcome";
import EditorMode from "@givewp/form-builder/types/editorMode";

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

    const {editorMode} = useFormState();
    const {selectBlock} = useDispatch('core/block-editor');

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
    }, [editorMode])

    useEffect(() => {

        const clickExitTourCallback = (event) => {
            var element = event.target as Element;
            if (tour.isActive() && element.classList.contains('js-exit-tour')) {
                tour.complete();
            }
        }

        document.addEventListener('click', clickExitTourCallback);

        return () => {
            window.removeEventListener('click', clickExitTourCallback);
        }
    }, [editorMode])

    useEffect(() => {
        const onTourComplete = () => {

            const data = new FormData();
            data.append('mode', editorMode);

            fetch(window.onboardingTourData.actionUrl, {
                method: 'POST',
                body: data,
            })

            // Trigger tools menu
            // Highlight
        }

        tour.on('complete', onTourComplete);

        return () => {
            tour.off('complete', onTourComplete);
        }
    }, [editorMode])

    return <></>
}

const Onboarding = () => {
    const {transfer, settings: {designId}} = useFormState();
    const {editorMode} = useFormState();
    const [showDesignSelector, setShowDesignSelector] = useState(!designId);
    const [showSchemaWelcome, setShowSchemaWelcome] = useState(!!window.onboardingTourData.autoStartSchemaTour);

    if (transfer.showUpgradeModal) {
        return null;
    }

    const steps = editorMode === 'schema' ? schemaSteps : designSteps;

    return <>
        <ShepherdTour steps={steps} tourOptions={options}>
            <TourEffectsAndEvents />
            {editorMode === EditorMode.design && showDesignSelector && <DesignSelector onContinue={() => setShowDesignSelector(false)} />}
            {editorMode === EditorMode.schema && showSchemaWelcome && <SchemaWelcome onContinue={() => setShowSchemaWelcome(false)} />}
        </ShepherdTour>
    </>
}

export default Onboarding;
