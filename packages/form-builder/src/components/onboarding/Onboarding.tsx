import {useContext, useEffect} from "react";
import {useDispatch} from "@wordpress/data";
import {ShepherdTour, ShepherdTourContext} from "react-shepherd";
import options from './options'
import steps from './steps'

import "shepherd.js/dist/css/shepherd.css";

declare global {
    interface Window {
        onboardingTourData?: {
            actionUrl: string;
            autoStartTour: boolean;
        };
    }
}

function TourEffectsAndEvents() {
    // @ts-ignore
    const tour = window.tour = useContext(ShepherdTourContext);

    const {selectBlock} = useDispatch('core/block-editor');

    useEffect(() => {
        const selectAmountBlockCallback = () => {
            const amountBlock = document.querySelector('[data-type="custom-block-editor/donation-amount-levels"]');
            const amountBlockId = amountBlock.getAttribute('data-block');
            selectBlock(amountBlockId).then(() => console.log('Amount block selected'));
        }

        document.addEventListener('selectAmountBlock', selectAmountBlockCallback );

        return () => {
            window.removeEventListener('selectAmountBlock', selectAmountBlockCallback);
        }
    }, [])

    useEffect(() => {

        const clickExitTourCallback = (event) => {
            var element = event.target as Element;
            if(tour.isActive() && element.classList.contains('js-exit-tour')){
                tour.complete();
            }
        }

        document.addEventListener('click', clickExitTourCallback);

        return () => {
            window.removeEventListener('click', clickExitTourCallback);
        }
    }, [])

    useEffect(() => {
        const onTourComplete = () => {
            fetch(window.onboardingTourData.actionUrl, {method: 'POST'})
        }

        tour.on('complete', onTourComplete );

        return () => {
            tour.off('complete', onTourComplete );
        }
    }, [])

    useEffect(() => {
        window.onboardingTourData.autoStartTour && ( tour.isActive() || tour.start() );
    }, [])

    return <></>
}

const Onboarding = () => {
    return <ShepherdTour steps={steps} tourOptions={options}>
        <TourEffectsAndEvents />
    </ShepherdTour>
}

export default Onboarding;
