import {render} from '@wordpress/element';
import Logo from '@givewp/form-builder/components/icons/logo';
import {Button} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';

const TextContent = ({title, description, stepNumber, stepCount, isFirst, isLast}) => {
    return (
        <div style={{textAlign: isFirst ? 'center' : 'initial'}}>
            {isFirst && (
                <div style={{display: 'flex', justifyContent: 'center', margin: '0 auto var(--givewp-spacing-4)'}}>
                    <Logo />
                </div>
            )}
            {!isFirst && !isLast && (
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
            )}
            <h3
                style={{
                    fontSize: isFirst || isLast ? '20px' : '16px',
                    margin: 'var(--givewp-spacing-3) 0' + (isFirst ? ' var(--givewp-spacing-5)' : ''),
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
        const stepCountAdjustedForBookends = steps.length - 2;
        const textContent = (
            <TextContent
                title={step.title}
                description={step.text}
                stepNumber={index}
                stepCount={stepCountAdjustedForBookends}
                isFirst={index === 0}
                isLast={index === steps.length - 1}
            />
        );
        const tempContainer = document.createElement('div');
        render(textContent, tempContainer);

        return {
            ...step,
            text: tempContainer.innerHTML,
        };
    });
};

export default withText;
