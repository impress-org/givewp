import React from 'react';
import {__} from '@wordpress/i18n';
import classnames from 'classnames';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import TimeSeriesChart from '@givewp/src/Admin/components/Charts/TimeSeriesChart';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonorOptionsWindowData} from '@givewp/donors/utils';
import styles from './styles.module.scss';

/**
 * @since 4.5.0
 */
interface DonorContributionsProps {
    donorId: number;
}

/**
 * @unreleased update the donations endpoint to account for sensitive data and anonymous donations
 * @since 4.5.0
 */
export default function DonorContributions({donorId}: DonorContributionsProps) {
    const {currency, mode} = getDonorOptionsWindowData();
    const donationChartEndpoint = `givewp/v3/donations?mode=${mode}&donorId=${donorId}&includeSensitiveData=true&anonymousDonations=include`;

    return (
        <OverviewPanel className={classnames(styles.contributionsCard)}>
            <Header
                title={__('Contributions', 'give')}
                subtitle={__("Shows the donor's contribution over time", 'give')}
                // href="#"
                // actionText={__('View Detailed Report', 'give')}
            />
            <TimeSeriesChart
                title={__('Contributions', 'give')}
                endpoint={donationChartEndpoint}
                amountFormatter={amountFormatter(currency)}
            />
        </OverviewPanel>
    );
}
