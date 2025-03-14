import {Icon} from "@wordpress/components";
import {close, external} from "@wordpress/icons";
import './styles.scss'

/**
 * @since 3.16.2
 */
const InspectorNotice = ({title, description, helpText, helpUrl, onDismiss}) => {

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
