import {forwardRef, useCallback, createRef, useEffect, useState} from 'react';
import styles from './style.module.scss';

const Switch = ({label, toggle, checked}) => {
    return (
        <label className={styles.label}>
            <input type="checkbox" aria-label="switch" checked={checked} onChange={() => toggle(!checked)} />
            <span className={styles.switch} />
            <span>{label && label}</span>
        </label>
    );
};

export default Switch;
