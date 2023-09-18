import type {ReactNode} from 'react';
import {BaseControl, Button, Path, SVG} from '@wordpress/components';
import classNames from 'classnames';
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
                    icon={buttonCaption ? null : EditIcon}
                    children={buttonCaption ? buttonCaption : null}
                />
            </div>
            {children}
        </BaseControl>
    );
}

export function EditIcon() {
    return (
        <SVG xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
            <Path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M5.069 2.251H8.25a.75.75 0 1 1 0 1.5H5.1c-.642 0-1.08 0-1.417.028-.329.027-.497.076-.614.136a1.5 1.5 0 0 0-.656.655c-.06.117-.108.286-.135.614-.027.338-.028.775-.028 1.417v6.3c0 .643 0 1.08.028 1.417.027.329.076.497.135.614a1.5 1.5 0 0 0 .656.656c.117.06.285.108.614.135.338.028.775.028 1.417.028h6.3c.642 0 1.08 0 1.417-.028.329-.027.497-.076.614-.135a1.5 1.5 0 0 0 .655-.656c.06-.117.109-.285.136-.614.027-.338.028-.774.028-1.417v-3.15a.75.75 0 0 1 1.5 0v3.181c0 .604 0 1.102-.033 1.508-.035.422-.109.81-.294 1.173a3 3 0 0 1-1.311 1.311c-.364.186-.752.26-1.173.294-.406.033-.904.033-1.508.033H5.069c-.604 0-1.102 0-1.508-.033-.421-.034-.809-.108-1.173-.294a3 3 0 0 1-1.311-1.31c-.185-.365-.26-.752-.294-1.174-.033-.406-.033-.904-.033-1.508V6.57c0-.604 0-1.102.033-1.508.035-.421.109-.809.294-1.173a3 3 0 0 1 1.311-1.31c.364-.186.752-.26 1.173-.295.406-.033.904-.033 1.508-.033z"
                fill="#737373"
            />
            <Path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M13.345 1.346a2.34 2.34 0 1 1 3.31 3.31L9.483 11.83l-.044.044c-.215.216-.406.406-.635.547-.201.123-.42.214-.65.269-.261.063-.53.063-.836.062H6a.75.75 0 0 1-.75-.75v-1.318c0-.305 0-.574.062-.836.055-.23.146-.449.27-.65.14-.23.33-.42.546-.635l.045-.044 7.172-7.172z"
                fill="#737373"
            />
        </SVG>
    );
}
