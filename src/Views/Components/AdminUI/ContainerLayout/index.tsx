import React, {ReactNode} from 'react';
import cx from 'classnames';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type ContainerProps = {
    children: ReactNode | ReactNode[];
};

export function Container({children}: ContainerProps) {
    return <div className={styles.mainContainer}>{children}</div>;
}

/**
 *
 * @unreleased
 */
export function LeftContainer({children}: ContainerProps) {
    return <div className={styles.leftContainer}>{children}</div>;
}

/**
 *
 * @unreleased
 */
export function RightContainer({children}: ContainerProps) {
    return <div className={styles.rightContainer}>{children}</div>;
}

/**
 *
 * @unreleased
 */

export type FieldSetContainerProps = {
    children: React.ReactNode;
    dropdown?: boolean;
};

export function FieldsetContainer({children, dropdown}: FieldSetContainerProps) {
    return (
        <fieldset
            className={cx(styles.fieldsetContainer, {
                [styles['dropdown']]: dropdown,
            })}
        >
            {children}
        </fieldset>
    );
}
