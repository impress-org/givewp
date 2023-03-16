import React from 'react';

import styles from './style.module.scss';
import {FormElementProps} from '@givewp/components/AdminUI/FormElements/types';

/**
 *
 * @unreleased
 */

const Form: React.FC<HTMLFormElement | FormElementProps> = ({children, id, onSubmit}) => (
    <form className={styles.form} id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

//Todo: TextFieldInput SelectFieldOptions Label Dropdown

export {Form};
