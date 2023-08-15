import {__} from '@wordpress/i18n';
import {useEffect} from '@wordpress/element';
import {Button, Popover} from '@wordpress/components';
import {close} from '@wordpress/icons';

type Props = {
    title: string;
    visible: boolean;
    onClose: () => void;
    children: React.ReactNode;
};

export default function StyledPopover({title, visible, onClose, children}: Props) {
    if (!visible) {
        return null;
    }

    useEffect(() => {
        return onClose;
    }, []);

    return (
        <Popover placement="left-end" variant={'unstyled'} focusOnMount={false}>
            <div
                style={{
                    background: 'var(--givewp-shades-white, #FFF)',
                    borderRadius: '0.5rem',
                    boxShadow: '0 0.25rem 0.5rem 0 #E6E6E6',
                    margin: '0.5rem',
                    width: '45rem',
                }}
            >
                <div
                    style={{
                        alignItems: 'center',
                        borderBottom: '1px solid #DDD',
                        display: 'flex',
                        justifyContent: 'space-between',
                        padding: '1rem 1.5rem',
                    }}
                >
                    <h1
                        style={{
                            fontSize: '1rem',
                            fontWeight: 600,
                            lineHeight: '1.5rem',
                            margin: 0,
                        }}
                    >
                        {title}
                    </h1>
                    <Button
                        onClick={onClose}
                        icon={close}
                        label={__('Close', 'give')}
                        style={{
                            height: '1.5rem',
                            minWidth: 'auto',
                            padding: '0',
                            width: '1.5rem',
                        }}
                    />
                </div>
                <div
                    style={{
                        maxHeight: '50vh',
                        overflow: 'auto',
                        padding: '1rem 1.5rem 1.5rem',
                    }}
                >
                    {children}
                </div>
            </div>
        </Popover>
    );
}
