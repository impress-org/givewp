import React from 'react';
import cx from 'classnames';

import styles from './GreenButton.module.css';

export default function GreenButton({as: Element = 'button', text, className, shadow = false, ...props}) {
    const classes = cx(styles.button, shadow && styles.shadow, className);

    if (Element === 'input') {
        return <input type="submit" value={text} className={classes} {...props} />;
    }

    return (
        <Element type="submit" className={classes} {...props}>
            {text}
        </Element>
    );
}
