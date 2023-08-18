import {Button} from '@wordpress/components';

export function CloseButton({onClick, label}) {
    return (
        <Button
            variant={'primary'}
            onClick={onClick}
            style={{
                zIndex: 11, // Above the modal header
                position: 'absolute',
                top: 0,
                right: '9rem',
                padding: 'var(--givewp-spacing-4) var(--givewp-spacing-12)',
                margin: 'var(--givewp-spacing-4) var(--givewp-spacing-6)',
            }}
        >
            {label}
        </Button>
    );
}

export function SetChangesButton({onClick, label}) {
    return (
        <Button
            variant={'secondary'}
            onClick={onClick}
            style={{
                zIndex: 11, // Above the modal header
                position: 'absolute',
                top: 0,
                right: 0,
                padding: 'var(--givewp-spacing-4) var(--givewp-spacing-6)',
                margin: 'var(--givewp-spacing-4) var(--givewp-spacing-6)',
            }}
        >
            {label}
        </Button>
    );
}
