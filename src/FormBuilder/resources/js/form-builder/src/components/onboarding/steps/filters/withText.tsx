import {createInterpolateElement, render} from '@wordpress/element';
import {Button} from '@wordpress/components';
import {__} from '@wordpress/i18n';

const TextContent = ({title, description, stepNumber, stepCount}) => {
    const stepCountText = createInterpolateElement(
        __('<strong>Step <stepNumber /></strong> of <stepCount />', 'give'),
        {
            strong: <strong />,
            stepNumber: <span>{stepNumber}</span>,
            stepCount: <span>{stepCount}</span>,
        }
    );

    return (
        <div className={'givewp-shepherd'}>
            <div className={'givewp-shepherd__steps'}>
                <div>{stepCountText}</div>
                <Button variant="link" className={'js-exit-tour'}>
                    {__('Exit tour', 'give')}
                </Button>
            </div>
            <h3
                className={'givewp-shepherd__title'}
                style={{
                    // @ts-ignore
                    textWrap: 'balance',
                }}
            >
                {title}
            </h3>
            <p className={'givewp-shepherd__description'}>{description}</p>
        </div>
    );
};

const withText = (steps) => {
    return steps.map((step, index) => {
        const showToolSteps = steps.some((step: {id: string}) => step.id === 'schema-find-tour');
        const stepCount = showToolSteps ? steps.length - 1 : steps.length;

        const Component = step.component;
        const content = (
            <TextContent title={step.title} description={step.text} stepNumber={index + 1} stepCount={stepCount} />
        );
        const tempContainer = document.createElement('div');
        render(Component ?? content, tempContainer);

        return {
            ...step,
            text: tempContainer.innerHTML,
        };
    });
};

export default withText;
