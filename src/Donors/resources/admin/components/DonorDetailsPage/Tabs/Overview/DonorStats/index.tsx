import React from 'react';
import {__} from '@wordpress/i18n';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {useDonorStatistics} from '@givewp/donors/hooks/useDonorStatistics';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonorOptionsWindowData} from '@givewp/donors/utils';

/**
 * @since 4.5.0
 */
interface DonorStatsProps {
    donorId: number;
}

/**
 * @since 4.5.0
 */
export default function DonorStats({donorId}: DonorStatsProps) {
    const {currency, mode} = getDonorOptionsWindowData();
    const {statistics: stats, isResolving: statsLoading, hasResolved: statsResolved} = useDonorStatistics(donorId, mode);

    return (
        <>
            <StatWidget
                label={__('Lifetime donations', 'give')}
                value={stats?.donations?.lifetimeAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />
            <StatWidget
                label={__('Highest donation', 'give')}
                value={stats?.donations?.highestAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />
            <StatWidget
                label={__('Average donation', 'give')}
                value={stats?.donations?.averageAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />
        </>
    );
}
