import {__} from '@wordpress/i18n'

import {FormNavigation} from "@givewp/components/AdminUI/types";

import styles from "./style.module.scss"

export default function FormNavigation({navigationalOptions, onSubmit, pageDescription, pageId, pageTitle}: FormNavigation) {
    return (
        <header className={styles.formPageNavigation}>
            <div className={styles.wrapper}>

                <div className={styles.container}>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.67 3.77 15.9 2 6 11.9l9.9 9.9 1.77-1.77-8.13-8.13 8.13-8.13z" fill="#0E0E0E" />
                    </svg>
                    <h1>{pageTitle}</h1>
                </div>

                <select>
                    {navigationalOptions?.map((option) => (
                        <option key={option.id}>{option.title}</option>
                    ))}
                </select>
            </div>

            <div className={styles.actions}>

                <div className={styles.pageDetails}>
                    <span>{pageDescription}:</span>
                    <span>#{pageId}</span>
                </div>

                <button type={'button'} className={styles.moreButton}>
                    {__('More Actions', 'give')}
                </button>

                <button type={'submit'} className={styles.saveButton} onSubmit={onSubmit}>
                    {__('Save Changes', 'give')}
                </button>

            </div>
        </header>
    );
}





