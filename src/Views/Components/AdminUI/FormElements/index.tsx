import React from 'react';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type FormElementProps = {
    children: React.ReactNode;
    onSubmit: React.FormEventHandler<HTMLFormElement>;
    id: string;
};

const Form: React.FC<HTMLFormElement | FormElementProps> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

export {Form};
