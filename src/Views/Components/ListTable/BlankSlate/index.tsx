import {__, sprintf} from '@wordpress/i18n';
import styles from './BlankSlate.module.scss';

interface BlankSlateProps {
    imagePath: string;
    imageAlt: string;
    table: string;
    href: string;
    linkText: string;
}

export default function BlankSlate({imagePath, table, imageAlt, href, linkText}: BlankSlateProps) {
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={imageAlt} />
            <h3>{sprintf(__("No %s's found", 'give'), table)}</h3>
            <p className={styles.helpMessage}>
                {__('Need help? learn more about', 'give')} <a href={href}>{linkText}</a>
            </p>
        </div>
    );
}
