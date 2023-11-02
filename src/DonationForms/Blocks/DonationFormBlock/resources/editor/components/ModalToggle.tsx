import React from 'react';

import '../styles/index.scss';

type ModalToggleProps = {
    onClick: () => void;
    children: React.ReactNode;
    classname?: string;
};

export function ModalToggle({onClick, classname, children}: ModalToggleProps) {
    return (
        <button className={classname} onClick={onClick}>
            {children}
        </button>
    );
}
