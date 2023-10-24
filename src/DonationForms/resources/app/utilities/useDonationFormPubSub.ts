import {RefObject, useEffect} from 'react';
import {iframeRef} from '@givewp/form-builder/components/canvas/DesignPreview';
import {FormSettings} from '@givewp/form-builder/types';
import {RequireAtLeastOne, FormGoal, FormColors} from '@givewp/forms/types';

/**
 * Events used in design mode
 */
export const PREVIEW_EVENTS = {
    SETTINGS: 'preview:settings',
    GOAL: 'preview:goal',
    COLORS: 'preview:colors',
}

/**
 * Simple Publish/Subscribe system used for handling form state in preview mode
 *
 * @unreleased
 */
export default function useDonationFormPubSub() {

    const events = {};

    useEffect(() => {
        addEventListener('message', eventListener, false);
        return () => removeEventListener('message', eventListener);
    }, []);

    const getMessage = (message: MessageEvent) => {
        // handle IframeResizer messages
        if (typeof message.data === 'string') {
            if (message.data.includes('[iFrameSizer]message:')) {
                return JSON.parse(message.data.replace('[iFrameSizer]message:', ''));
            }
        }

        return message.data
    }

    const eventListener = (message: MessageEvent) => {
        const {event, data} = getMessage(message);

        if (events[event]) {
            events[event].forEach((callback: (data: any) => void) => {
                callback(data);
            });
        }
    }

    const subscribe = (event: string, callback: Function) => {
        if (!events[event]) {
            events[event] = [];
        }

        events[event].push(callback);
    }

    const publish = (event: string, data: any, iframeRef: RefObject<any>) => {
        if (iframeRef.current?.sendMessage) {
            iframeRef.current.sendMessage({
                event,
                data
            });
        }
    }

    const publishSettings = (data: RequireAtLeastOne<FormSettings>) => {
        publish(PREVIEW_EVENTS.SETTINGS, data, iframeRef)
    }

    const publishGoal = (data: RequireAtLeastOne<FormGoal>) => {
        publish(PREVIEW_EVENTS.GOAL, data, iframeRef)
    }

    const publishColors = (data: RequireAtLeastOne<FormColors>) => {
        publish(PREVIEW_EVENTS.COLORS, data, iframeRef)
    }

    const subscribeToSettings = (callback: (data: FormSettings) => void) => {
        subscribe(PREVIEW_EVENTS.SETTINGS, callback)
    }

    const subscribeToGoal = (callback: (data: FormGoal) => void) => {
        subscribe(PREVIEW_EVENTS.GOAL, callback)
    }

    const subscribeToColors = (callback: (data: FormColors) => void) => {
        subscribe(PREVIEW_EVENTS.COLORS, callback)
    }

    /**
     * Unsubscribe from event
     *
     * @param event
     */
    const unsubscribe = (event: string) => {
        if (events[event]) {
            delete events[event];
        }
    }

    /**
     * Unsubscribe from all event
     */
    const unsubscribeAll = () => {
        for (const key in PREVIEW_EVENTS) {
            delete events[key];
        }
    }

    return {
        unsubscribe,
        unsubscribeAll,
        publishGoal,
        publishColors,
        publishSettings,
        subscribeToGoal,
        subscribeToColors,
        subscribeToSettings,
    }
}

/**
 * Helper function for setting goal props in design mode
 *
 * @param type
 */
export const setGoalType = (type: string) => {
    return {
        type,
        label: type,
        typeIsCount: 'amount' !== type,
        typeIsMoney: 'amount' === type
    }
}
