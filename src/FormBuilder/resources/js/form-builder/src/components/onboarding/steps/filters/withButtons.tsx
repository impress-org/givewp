import {__} from '@wordpress/i18n';

const withButtons = (steps) => {
    const previous = {
        classes: 'shepherd-button-secondary',
        text: __('Previous', 'give'),
        type: 'back',
    };

    const next = {
        classes: 'shepherd-button-primary',
        text: __('Next', 'give'),
        type: 'next',
    };

    const nextVariant = {
        classes: 'shepherd-button-primary',
        text: __('Got it', 'give'),
        type: 'next',
    };

    const complete = {
        classes: 'shepherd-button-primary',
        text: __('Got it', 'give'),
        type: 'complete',
    };

    const okay = {
        classes: 'shepherd-button-primary shepherd-button-primary--tools',
        text: __('Okay', 'give'),
        type: 'complete',
    };

    const hasToolSteps = steps.some(({id}) => id === 'schema-find-tour');

    return steps.map((step, index) => {
        if (index === 0) {
            return {
                ...step,
                ...{
                    buttons: [next],
                },
            };
        }

        if (step.id === 'schema-find-tour') {
            return {
                ...step,
                ...{
                    buttons: [okay],
                },
            };
        }

        if (hasToolSteps && step.id === 'schema-edit-block') {
            return {
                ...step,
                ...{
                    buttons: [previous, nextVariant],
                },
            };
        }

        if (index === steps.length - 1) {
            return {
                ...step,
                ...{
                    buttons: [previous, complete],
                },
            };
        }

        return {
            ...step,
            ...{
                buttons: [previous, next],
            },
        };
    });
};

export default withButtons;
