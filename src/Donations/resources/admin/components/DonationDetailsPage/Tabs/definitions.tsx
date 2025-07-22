import { __ } from '@wordpress/i18n';
import OverviewTab from './Overview';
import RecordsTab from './Records';
import { Tab } from '@givewp/components/AdminDetailsPage/types';

/**
 * @since 4.6.0
 */
const donationDetailsTabs: Tab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: OverviewTab,
    },
    {
        id: 'records',
        title: __('Records', 'give'),
        content: RecordsTab,
    },
];

export default donationDetailsTabs;
