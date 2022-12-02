import type {Element} from '@givewp/forms/types';
import classNames from 'classnames';
import type {NodeWrapperProps} from '@givewp/forms/propTypes';

export default function NodeWrapper({nodeType, type, name, htmlTag: Element = 'div', children}: NodeWrapperProps) {
    return (
        <Element
            className={classNames({
                [`givewp-${nodeType}`]: nodeType,
                [`givewp-${nodeType}-${type}`]: type,
                [`givewp-${nodeType}-${type}-${name}`]: name,
            })}
        >
            {children}
        </Element>
    );
}
