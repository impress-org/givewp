import {render} from '@wordpress/element';
import {Button} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';

const TextContent = ({title, description, stepNumber, stepCount, isLast}) => {
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
                <div>{sprintf(__('Step %s of %s', 'give'), stepNumber, stepCount)}</div>
                <Button variant="link" className={'js-exit-tour'}>
                    {__('Exit tour', 'give')}
                </Button>
            </div>
            <h3
                style={{
                    fontSize: isLast ? '20px' : '16px',
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
                isLast={index === steps.length - 1}
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
