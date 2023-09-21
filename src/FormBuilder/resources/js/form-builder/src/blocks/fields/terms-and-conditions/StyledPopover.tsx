import {__} from '@wordpress/i18n';
import {useEffect} from '@wordpress/element';
import {Button, Popover} from '@wordpress/components';
import {close} from '@wordpress/icons';
import './styles.scss';

type Props = {
    title: string;
    visible: boolean;
    onClose: () => void;
    children: React.ReactNode;
};

export default function StyledPopover({title, visible, onClose, children}: Props) {
    useEffect(() => {
        return onClose;
    }, []);

    if (!visible) {
        return null;
    }

    return (
        <Popover
            className="givewp-styled-popover-content-settings"
            placement="left-end"
            variant={'unstyled'}
            focusOnMount={false}
        >
            <div className="givewp-styled-popover-content-settings__header">
                <h1>{title}</h1>
                <Button onClick={onClose} icon={close} label={__('Close', 'give')} />
            </div>
            <div className="givewp-styled-popover-content-settings__content">{children}</div>
        </Popover>
    );
}
