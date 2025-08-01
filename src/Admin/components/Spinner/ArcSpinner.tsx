import styles from './style.module.scss';

/**
 * @since 4.6.0
 */
export default function ArcSpinner() {
    return (
        <svg className={styles.arcSpinner} width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#uszxunxu7a)">
                <path d="M1.568 9.202C3.11 3.441 9.033.022 14.795 1.566c5.761 1.544 9.18 7.466 7.637 13.227" stroke="#fff" stroke-width="1.8"/>
            </g>
            <defs>
                <clipPath id="uszxunxu7a">
                    <path fill="#fff" d="M0 0h24v24H0z"/>
                </clipPath>
            </defs>
        </svg>
    );
}
