import {__} from "@wordpress/i18n";
import styles from "./DonationForms.module.scss";
import cx from "classnames";

export const columns = [
    {
        name: 'id',
        text: __('ID', 'give'),
        preset: 'idBadge'
    },
    {
        name: 'name',
        text: __('Name', 'give'),
        heading: true,
        render: (item) => <a href={item.edit}>{item.name}</a>
    },
    {
        name: 'amount',
        text: __('Amount', 'give'),
        addClass: styles.monetary,
    },
    {
        name: 'goal',
        text: __('Goal', 'give'),
        render: (form) => {
            if (!form.goal) {
                return __('No Goal Set', 'give');
            } else {
                return (
                    <>
                        <div className={styles.goalProgress}>
                            <span
                                style={{
                                    width: form.goal.format == 'percentage' ?
                                        form.goal.actual :
                                        Math.max(Math.min(form.goal.progress, 100), 0) + '%'
                                }}
                            />
                        </div>
                        <span className={cx({[styles.monetary]: form.goal.format == __('amount', 'give')})}>
                            {form.goal.actual}
                        </span>
                        {form.goal.format != 'percentage' && (
                            <>
                                {' '}
                                {__('of', 'give')}{' '}
                                <a href={`${form.edit}&give_tab=donation_goal_options`}>
                                    {form.goal.goal}
                                    {form.goal.format != __('amount', 'give') ? ` ${form.goal.format}` : null}
                                </a>
                            </>
                        )}
                        {form.goal.progress >= 100 && (
                            <p>
                                <span className={cx('dashicons dashicons-star-filled', styles.star)}></span>
                                {__('Goal achieved!', 'give')}
                            </p>
                        )}
                    </>
                )
            }
        }
    },
    {
        name: 'donations',
        text: __('Donations', 'give'),
        render: (form) => (
            <a href={`edit.php?post_type=give_forms&page=give-payment-history&form_id=${form.id}`}>
                {form.donations}
            </a>
        ),
    },
    {
        name: 'revenue',
        text: __('Revenue', 'give'),
        render: (form) => (
            <a href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=${form.id}`}>
                {form.revenue}
            </a>
        )
    },
    {
        name: 'shortcode',
        text: __('Shortcode', 'give'),
        render: (form) => (
            <input
                type={"text"}
                aria-label={__('Copy shortcode', 'give')}
                readOnly
                value={form.shortcode}
                className={styles.shortcode}
            />
        )
    },
    {
        name: 'datetime',
        text: __('Date', 'give'),
    },
    {
        name: 'status',
        text: __('Status', 'give'),
        preset: 'statusBadge'
    },
];
