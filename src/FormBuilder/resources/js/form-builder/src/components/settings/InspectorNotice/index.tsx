import {CSSProperties} from "react";
import {__} from "@wordpress/i18n";
import {Icon} from "@wordpress/components";
import {close, external} from "@wordpress/icons";
import './styles.scss'

const InspectorNotice = ({title, description, helpText, helpUrl, onDismiss}) => {

    // const onClose = () => {
    //     fetch(window.goalNotificationData.actionUrl, {method: 'POST'})
    //         .then(() => {
    //             setShowNotice(false);
    //         });
    // }

    return (
        <div className='givewp-inspector-notice__container'>
            <span className='givewp-inspector-notice__title'>
                {title}
                <Icon icon={close} className='givewp-inspector-notice__closeIcon' onClick={onDismiss} />
            </span>
            <span>
                {description}
            </span>
            <span>
                <a href={helpUrl} target="_blank">
                    <Icon icon={external} className='givewp-inspector-notice__externalIcon' />
                    {helpText}
                </a>
            </span>
        </div>
    )
}

export default InspectorNotice;
