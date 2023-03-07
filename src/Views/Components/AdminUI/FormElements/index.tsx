import React from 'react';

import styles from './style.module.scss';
import {Form} from '../types';

const Form: React.FC<HTMLFormElement | Form> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

//Todo: TextFieldInput SelectFieldOptions Label Dropdown

export {Form};
