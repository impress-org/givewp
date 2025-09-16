import {useState} from 'react';
import styles from './style.module.scss';
import cx from 'classnames';

interface ToggleSwitchProps {
    onChange: React.Dispatch<React.SetStateAction<boolean>>;
    initialChecked: boolean;
    ariaLabel?: string;
}

const ToggleSwitch = ({ariaLabel, initialChecked, onChange}: ToggleSwitchProps) => {
    const [checked, setChecked] = useState(initialChecked);

    const handleChange = () => {
        setChecked(state => {
            onChange(!state);
            return !state;
        })
    };

    return (
        <button className={styles.container} onClick={handleChange}>
            <span className={cx(styles.switch, { [styles.checked]: checked })} />
            {ariaLabel && <span>{ariaLabel}</span>}
        </button>
    );
};

export default ToggleSwitch;
