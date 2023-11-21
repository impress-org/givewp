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
        <div>
            <div
                style={{
                    display: 'flex',
                    backgroundColor: 'var(--givewp-blue-25)',
                    fontSize: '12px',
                    padding: 'var(--givewp-spacing-1) var(--givewp-spacing-3)',
                    borderRadius: '2px',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                }}
            >
                <div>{stepCountText}</div>
                <Button variant="link" className={'js-exit-tour'}>
                    {__('Exit tour', 'give')}
                </Button>
            </div>
            <h3
                style={{
                    fontSize: '16px',
                    margin: 'var(--givewp-spacing-3) 0',
                    // @ts-ignore
                    textWrap: 'balance',
                }}
            >
                {title}
            </h3>
            <p style={{fontSize: '14px'}}>{description}</p>
        </div>
    );
};

const withText = (steps) => {
    return steps.map((step, index) => {
        const Component = step.component;
        const content = (
            <TextContent
                title={step.title}
                description={step.text}
                stepNumber={index + 1}
                stepCount={steps.length}
            />
        );
        const tempContainer = document.createElement('div');
        render( Component ?? content, tempContainer);

        return {
            ...step,
            text: tempContainer.innerHTML,
        };
    });
};

export default withText;
