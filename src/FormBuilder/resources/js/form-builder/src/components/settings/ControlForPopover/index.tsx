import type {ReactNode} from 'react';
import {BaseControl, Button} from '@wordpress/components';
import {moreVertical} from '@wordpress/icons';
import classNames from 'classnames';
import './styles.scss';


/**
 * @0.6.0
 */
interface ControlForPopoverProps {
    id: string;
    help: string;
    heading: string;
    onButtonClick: () => void;
    children: ReactNode;
    isButtonActive: boolean;
}

/**
 * @0.6.0
 */
export default function ControlForPopover({id, help, heading, children, onButtonClick, isButtonActive}: ControlForPopoverProps) {
    return (
        <BaseControl id={id} help={help}>
            <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between'}}>
                <span>{heading}</span>
                <Button
                    className={classNames('givewp-control-popover-setting-button', {
                        'givewp-control-popover-setting-button--active': isButtonActive,
                    })}
                    onClick={onButtonClick}
                    icon={moreVertical}
                />
            </div>
            {children}
        </BaseControl>
    );
}