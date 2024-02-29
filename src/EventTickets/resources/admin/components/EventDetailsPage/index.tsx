import cx from 'classnames';
import {__} from '@wordpress/i18n';
import {GiveIcon} from '@givewp/components';
import styles from './EventDetailsPage.module.scss';

export default function EventDetailsPage() {
    return (
        <>
            <article className={styles.page}>
                <header className={styles.pageHeader}>
                    <div className={styles.flexRow}>
                        <GiveIcon size={'1.875rem'} />
                        <h1 className={styles.pageTitle}>{__('Event details', 'give')}</h1>
                    </div>
                    <div className={styles.flexRow}>
                        <a
                            href={`${window.GiveEventTickets.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets`}
                            className={`button button-secondary ${styles.goToEventsListButton}`}
                        >
                            {__('Go to events list', 'give')}
                        </a>
                    </div>
                </header>
                <div className={cx('wp-header-end', 'hidden')} />
                <div className={styles.pageContent}>{window.GiveEventTickets.event.title}</div>
            </article>
        </>
    );
}
