import {__} from '@wordpress/i18n';
import {useEffect, useState} from 'react';
import RevenueChart from '../RevenueChart';
import GoalProgressChart from '../GoalProgressChart';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import HeaderText from '../HeaderText';
import HeaderSubText from '../HeaderSubText';
import DefaultFormWidget from '../DefaultForm';
import StatWidget from './StatWidget';
import DateRangeFilters from './DateRangeFilters';
import {amountFormatter, getCampaignOptionsWindowData, useCampaignEntityRecord} from '@givewp/campaigns/utils';
import type {CampaignOverViewStat} from './types';
import styles from './styles.module.scss';
import CampaignDetailsErrorBoundary
    from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/CampaignDetailsErrorBoundary';

const campaignId = new URLSearchParams(window.location.search).get('id');

const {currency} = getCampaignOptionsWindowData();
const currencyFormatter = amountFormatter(currency);

export const filterOptions = [
    {label: __('Today', 'give'), value: 1, description: __('from today', 'give')},
    {label: __('Last 7 days', 'give'), value: 7, description: __('from the last 7 days', 'give')},
    {label: __('Last 30 days', 'give'), value: 30, description: __('from the last 30 days', 'give')},
    {label: __('Last 90 days', 'give'), value: 90, description: __('from the last 90 days', 'give')},
    {label: __('All-time', 'give'), value: 0, description: __('total for all-time', 'give')},
];

const fetchCampaignOverviewStats = async (days: number, setLoading: Function, setStats: Function) => {
        setLoading(true);

        try {
            const response = await apiFetch({
                path: addQueryArgs(`/givewp/v3/campaigns/${campaignId}/statistics`, {rangeInDays: days}),
            });

            setStats(response as CampaignOverViewStat[]);
            setLoading(false);
        } catch (error) {
            console.error('Error fetching campaign stats:', error);
            setLoading(false);
        }
    };

const CampaignStats = () => {
    const [dayRange, setDayRange] = useState<number>(0);
    /**
     * The stats array is currently either an array size of 1 or 2
     * if the array size is 1 it means all-time stats
     * if the array size is 2 it means the stats for the day range was selected
     * the second element in the array is the query for the previous day range
     */
    const [stats, setStats] = useState<CampaignOverViewStat[]>([]);
    const {campaign} = useCampaignEntityRecord();
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        fetchCampaignOverviewStats(dayRange, setLoading, setStats);
    }, [dayRange]);

    const widgetDescription = filterOptions.find((option) => option.value === dayRange)?.description;

    const amountRaised = stats.length > 0 ? stats[0].amountRaised : 0;
    const previousAmountRaised = stats.length > 1 ? stats[1].amountRaised : null;
    const donationCount = stats.length > 0 ? stats[0].donationCount : 0;
    const previousDonationCount = stats.length > 1 ? stats[1].donationCount : null;
    const donorCount  = stats.length > 0 ? stats[0].donorCount : 0;
    const previousDonorCount = stats.length > 1 ? stats[1].donorCount : null;

    return (
        <>
            <DateRangeFilters selected={dayRange} options={filterOptions} onSelect={(value) => setDayRange(value)} />
            <div className={styles.mainGrid}>
                <StatWidget
                    label={__('Amount raised', 'give')}
                    value={amountRaised}
                    previousValue={previousAmountRaised}
                    description={widgetDescription}
                    formatter={currencyFormatter}
                    loading={loading}
                />
                <StatWidget
                    label={__('Number of donations', 'give')}
                    value={donationCount}
                    previousValue={previousDonationCount}
                    description={widgetDescription}
                    loading={loading}
                />
                <StatWidget
                    label={__('Number of donors', 'give')}
                    value={donorCount}
                    previousValue={previousDonorCount}
                    description={widgetDescription}
                    loading={loading}
                />
                <div className={styles.revenueWidget}>
                    <header className={styles.headerSpacing}>
                        <HeaderText>{__('Revenue', 'give')}</HeaderText>
                        <HeaderSubText>
                            {__('This graph shows revenue for the campaign over its lifetime.', 'give')}
                        </HeaderSubText>
                    </header>
                    <CampaignDetailsErrorBoundary>
                        <RevenueChart />
                    </CampaignDetailsErrorBoundary>
                </div>
                <div className={styles.nestedGrid}>
                    <div className={styles.progressWidget}>
                        <header className={styles.headerSpacing}>
                            <HeaderText>{__('Goal progress', 'give')}</HeaderText>
                            <HeaderSubText>{__('This chart shows your campaign goal progress.', 'give')}</HeaderSubText>
                        </header>
                        <CampaignDetailsErrorBoundary>
                            <GoalProgressChart value={campaign.goalStats.actual} goal={campaign.goal} goalType={campaign.goalType} />
                        </CampaignDetailsErrorBoundary>
                    </div>
                    <DefaultFormWidget defaultForm={campaign.defaultFormTitle} />
                </div>
            </div>
        </>
    );
};

export default CampaignStats;
