import React from 'react';

import {Form} from '../types';

const Form: React.FC<HTMLFormElement | Form> = ({children, id, onSubmit}) => (
    <form id={id} onSubmit={onSubmit}>
        {children}
    </form>
);

//Todo: TextFieldInput SelectFieldOptions Label Dropdown

export {Form};
