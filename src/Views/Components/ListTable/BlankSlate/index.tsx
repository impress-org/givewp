import {__} from '@wordpress/i18n';
import styles from './BlankSlate.module.scss';

interface BlankSlateProps {
    imagePath: string;
    description: string;
    href: string;
    linkText: string;
}

export default function BlankSlate({imagePath, description, href, linkText}: BlankSlateProps) {
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={description} />
            <h3>{description}</h3>
            <p className={styles.helpMessage}>
                {__('Need help? Learn more about', 'give')}{' '}
                <a target="_blank" href={href}>
                    {linkText}
                </a>
            </p>
        </div>
    );
}
