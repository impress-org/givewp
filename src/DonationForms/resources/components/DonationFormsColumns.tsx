import {__} from '@wordpress/i18n';
import {ListTableColumn} from '@givewp/components';

export const donationFormsColumns: Array<ListTableColumn> = [
    {
        id: 'id',
        label: __('ID', 'give'),

        sortable: true,
    },
    {
        id: 'name',
        label: __('Name', 'give'),
        sortable: true,

        // render: (form: {edit; name}) => <a href={form.edit}>{form.name}</a>,
    },
    {
        id: 'amount',
        label: __('Donation Levels', 'give'),
        sortable: false,
    },
    {
        id: 'goal',
        label: __('Goal', 'give'),
        sortable: false,

        // render: (form: {goal; edit; id}) => {
        //     if (!form.goal) {
        //         return <>{__('No Goal Set', 'give')}</>;
        //     }
        //     const goalPercentage =
        //         form.goal.format == 'percentage'
        //             ? form.goal.actual
        //             : Math.max(Math.min(form.goal.progress, 100), 0) + '%';
        //     return (
        //         <>
        //             <div
        //                 role="progressbar"
        //                 aria-labelledby={`giveDonationFormsProgressBar-${form.id}`}
        //                 aria-valuenow={goalPercentage.slice(0, -1)}
        //                 aria-valuemin={0}
        //                 aria-valuemax={100}
        //                 className={styles.goalProgress}
        //             >
        //                 <span
        //                     style={{
        //                         width: goalPercentage,
        //                     }}
        //                 />
        //             </div>
        //             <div id={`giveDonationFormsProgressBar-${form.id}`}>
        //                 <span className={cx({[styles.monetary]: form.goal.format == __('amount', 'give')})}>
        //                     {form.goal.actual}
        //                 </span>
        //                 {form.goal.format != 'percentage' && (
        //                     <>
        //                         {' '}
        //                         {__('of', 'give')}{' '}
        //                         <a href={`${form.edit}&give_tab=donation_goal_options`}>
        //                             {form.goal.goal}
        //                             {form.goal.format != __('amount', 'give') ? ` ${form.goal.format}` : null}
        //                         </a>
        //                     </>
        //                 )}
        //                 {form.goal.progress >= 100 && (
        //                     <p>
        //                         <span className={cx('dashicons dashicons-star-filled', styles.star)}></span>
        //                         {__('Goal achieved!', 'give')}
        //                     </p>
        //                 )}
        //             </div>
        //         </>
        //     );
        // },
    },
    {
        id: 'donations',
        label: __('Donations', 'give'),
        sortable: true,
        //
        // render: (form: {donations; id}) => (
        //     <a href={`edit.php?post_type=give_forms&page=give-payment-history&form_id=${form.id}`}>{form.donations}</a>
        // ),
    },
    {
        id: 'revenue',
        label: __('Revenue', 'give'),
        sortable: false,

        // render: (form: {revenue; id}) => (
        //     <a href={`edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=${form.id}`}>
        //         {form.revenue}
        //     </a>
        // ),
    },
    {
        id: 'shortcode',
        label: __('Shortcode', 'give'),
        sortable: true,

        // render: (form: {shortcode}) => (
        //     <input
        //         type={'text'}
        //         aria-label={__('Copy shortcode', 'give')}
        //         readOnly
        //         value={form.shortcode}
        //         className={styles.shortcode}
        //     />
        // ),
    },
    {
        id: 'datetime',
        label: __('Date', 'give'),
        sortable: true,
    },
    {
        id: 'status',
        label: __('Status', 'give'),
        sortable: true,
    },
];
