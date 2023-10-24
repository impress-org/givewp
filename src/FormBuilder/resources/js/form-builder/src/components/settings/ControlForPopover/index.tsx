import type {ReactNode} from 'react';
import {BaseControl, Button} from '@wordpress/components';
import classNames from 'classnames';
import PopoverEditIcon from './PopoverEditIcon';
import './styles.scss';

/**
 * @since 3.0.0
 */
interface ControlForPopoverProps {
    id: string;
    help: string;
    heading: string;
    buttonCaption?: string;
    onButtonClick: () => void;
    children: ReactNode;
    isButtonActive: boolean;
}

/**
 * @since 3.0.0
 */
export default function ControlForPopover({
    id,
    help,
    heading,
    buttonCaption,
    children,
    onButtonClick,
    isButtonActive,
}: ControlForPopoverProps) {
    return (
        <BaseControl id={id} help={help}>
            <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between'}}>
                <span>{heading}</span>
                <Button
                    className={classNames('givewp-control-popover-setting-button', {
                        'givewp-control-popover-setting-button--active': isButtonActive,
                        'givewp-control-popover-setting-button--has-caption': buttonCaption !== '',
                    })}
                    onClick={onButtonClick}
                    icon={buttonCaption ? null : PopoverEditIcon}
                    children={buttonCaption ? buttonCaption : null}
                />
            </div>
            {children}
        </BaseControl>
    );
}
