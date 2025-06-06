/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import styles from './styles.module.scss';

/**
 * @unreleased
 */
export default function BlankSlate() {
    return (
        <div className={styles.blankSlate}>
            <div className={styles.icon}>
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="32" fill="#F3F4F6"/>
                    <path d="M32 20c-6.627 0-12 5.373-12 12 0 6.627 5.373 12 12 12s12-5.373 12-12c0-6.627-5.373-12-12-12zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10-10-4.477-10-10 4.477-10 10-10z" fill="#9CA3AF"/>
                    <path d="M32 26c-3.314 0-6 2.686-6 6s2.686 6 6 6 6-2.686 6-6-2.686-6-6-6zm0 2c2.209 0 4 1.791 4 4s-1.791 4-4 4-4-1.791-4-4 1.791-4 4-4z" fill="#9CA3AF"/>
                    <path d="M28 38h8v2h-8v-2z" fill="#9CA3AF"/>
                </svg>
            </div>
            <div className={styles.content}>
                <h3 className={styles.title}>
                    {__('This donor does not have any address saved.', 'give')}
                </h3>
            </div>
        </div>
    );
}
