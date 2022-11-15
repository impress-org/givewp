import {forwardRef, useCallback, createRef, useEffect, useState} from 'react';
import styles from './style.module.scss';

const Switch = ({label, setMode, mode}) => {
    return (
        <label className={styles.label}>
            <input type="checkbox" aria-label="switch" checked={mode} onChange={() => setMode(!mode)} />
            <span className={styles.switch} />
            <span>{label && label}</span>
        </label>
    );
};

export default Switch;
