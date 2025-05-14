import {stepConfig} from '@givewp/campaigns/admin/components/ExistingUserIntroModal/index';
import styles from './ExistingUserIntroModal.module.scss';

/**
 * @since 4.0.0
 */

type StepDetailsProps = {
    stepConfig: stepConfig;
    handleClick: () => void;
};

export function StepDetails({stepConfig, handleClick}: StepDetailsProps) {
    return (
        <div className={styles.details}>
            {stepConfig.badge && stepConfig.badge()}

            <strong className={styles.title}>{stepConfig.title}</strong>
            <p className={styles.description}>{stepConfig.description}</p>

            <div className={styles.actions}>
                {stepConfig.linkText && (
                    <a
                        href="https://docs.givewp.com/campaigns-release"
                        target="_blank"
                        rel="noreferrer"
                        className={`${styles.button} ${styles.link}`}
                    >
                        {stepConfig.linkText}
                    </a>
                )}

                {stepConfig.buttonText && (
                    <button className={styles.button} onClick={handleClick}>
                        {stepConfig.buttonText}
                    </button>
                )}
            </div>
        </div>
    );
}
