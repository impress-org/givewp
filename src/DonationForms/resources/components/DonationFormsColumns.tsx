import {__} from "@wordpress/i18n";
import styles from "./DonationFormsColumns.module.scss";
import cx from "classnames";
import {ListTableColumn} from "@givewp/components";

export const donationFormsColumns: Array<ListTableColumn> = [
    {
        name: 'id',
        text: __('ID', 'give'),
        inlineSize: '4rem',
        preset: 'idBadge'
    },
    {
        name: 'name',
        text: __('Name', 'give'),
        inlineSize: '10rem',
        heading: true,
        render: (form: {edit, name}) => <a href={form.edit}>{form.name}</a>,
    },
    {
        name: 'amount',
        text: __('Donation Levels', 'give'),
        preset: 'monetary',
    },
    {
        name: 'goal',
        text: __('Goal', 'give'),
        render: (form: {goal, edit, id}) => {
            if (!form.goal) {
                return <>{__('No Goal Set', 'give')}</>;
            }
            const goalPercentage = form.goal.format == 'percentage' ? form.goal.actual :
                Math.max(Math.min(form.goal.progress, 100), 0) + '%';
            return (
                <>
                    <div role="progressbar" aria-labelledby={`giveDonationFormsProgressBar-${form.id}`} aria-valuenow={goalPercentage.slice(0, -1)}
                         aria-valuemin={0} aria-valuemax={100} className={styles.goalProgress}
                    >
                        <span
                            style={{
                                width: goalPercentage
                            }}
                        />
                    </div>
                    <div id={`giveDonationFormsProgressBar-${form.id}`}>
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
                    </div>
                </>
            )
        }
    },
    {
        name: 'donations',
        text: __('Donations', 'give'),
        inlineSize: '6rem',
        render: (form: {donations, id}) => (
            <a href={`edit.php?post_type=give_forms&page=give-payment-history&form_id=${form.id}`}>
                {form.donations}
            </a>
        ),
    },
    {
        name: 'revenue',
        text: __('Revenue', 'give'),
        inlineSize: '6rem',
        render: (form: {revenue, id}) => (
            <a href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=${form.id}`}>
                {form.revenue}
            </a>
        )
    },
    {
        name: 'shortcode',
        text: __('Shortcode', 'give'),
        inlineSize: '10rem',
        render: (form: {shortcode}) => (
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
        preset: 'postStatus'
    },
];
