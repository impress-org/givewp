import {__} from '@wordpress/i18n';
import {useEffect, useState} from "react";
import RevenueChart from "../RevenueChart";
import GoalProgressChart from "../GoalProgressChart";
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import HeaderText from '../HeaderText';
import HeaderSubText from '../HeaderSubText';
import DefaultFormWidget from "../DefaultForm";
import {useCampaignEntityRecord, amountFormatter} from '@givewp/campaigns/utils';
import {getCampaignDetailsWindowData} from '@givewp/campaigns/admin/common';

import styles from "./styles.module.scss"

const campaignId = new URLSearchParams(window.location.search).get('id');

const {currency} = getCampaignDetailsWindowData();
const currencyFormatter = amountFormatter(currency);

const pluck = (array: any[], property: string) => array.map(element => element[property])

const filterOptions = [
    {label: __('Today', 'give'), value: 1, description: __('from today', 'give')},
    {label: __('Last 7 days', 'give'), value: 7, description: __('from the last 7 days', 'give')},
    {label: __('Last 30 days', 'give'), value: 30, description: __('from the last 30 days', 'give')},
    {label: __('Last 90 days', 'give'), value: 90, description: __('from the last 90 days', 'give')},
    {label: __('All-time', 'give'), value: 0, description: __('total for all-time', 'give')},
]

const CampaignStats = () => {

    const [dayRange, setDayRange] = useState(null);
    const [stats, setStats] = useState([]);
    const {campaign} = useCampaignEntityRecord();

    useEffect(() => {
        onDayRangeChange(0)
    }, [])

    const onDayRangeChange = async (days: number) => {
        setDayRange(days)

        apiFetch({path: addQueryArgs('/give-api/v2/campaigns/' + campaignId + '/statistics', {rangeInDays: days})})
            .then(setStats);
    }

    const widgetDescription = filterOptions.find(option => option.value === dayRange)?.description

    return (
        <>
            <DateRangeFilters selected={dayRange} options={filterOptions} onSelect={onDayRangeChange} />
            <div className={styles.mainGrid}>
                <StatWidget label={__('Amount raised', 'give')} values={pluck(stats, 'amountRaised')}
                            description={widgetDescription} formatter={currencyFormatter} />
                <StatWidget label={__('Number of donations', 'give')} values={pluck(stats, 'donationCount')}
                            description={widgetDescription} />
                <StatWidget label={__('Number of donors', 'give')} values={pluck(stats, 'donorCount')}
                            description={widgetDescription} />
                <RevenueWidget />
                <div className={styles.nestedGrid}>
                    <GoalProgressWidget />
                    <DefaultFormWidget defaultForm={campaign.defaultFormTitle} />
                </div>
            </div>
        </>
    )
}

const FooterText = ({children}) => {
    return (
        <div className={styles.footerText}>
            {children}
        </div>
    )
}

const DisplayText = ({children}) => {
    return (
        <div className={styles.statWidgetDisplay}>
            {children}
        </div>
    )
}

const StatWidget = ({label, values, description, formatter = null}) => {
    return (
        <div className={styles.statWidget}>
            <header>
                <HeaderText>{label}</HeaderText>
            </header>
            <div className={styles.statWidgetAmount}>
                <DisplayText>
                    {'undefined' !== typeof values[0]
                        ? formatter?.format(values[0]) ?? values[0]
                        : <span>&nbsp;</span>
                    }
                </DisplayText>
                {!!values[1] && (
                    <PercentChangePill value={values[0]} comparison={values[1]} />
                )}
            </div>
            <footer>
                <FooterText>{description}</FooterText>
            </footer>
        </div>
    )
}

const PercentChangePill = ({value, comparison}) => {

    const change = Math.round(100 * ((value - comparison) / comparison)) ?? 0

    const [color, backgroundColor, symbol] = change == 0
        ? ['#060c1a', '#f2f2f2', '⯈']
        : change > 0
            ? ['#2d802f', '#f2fff3', '⯅']
            : ['#e35f45', '#fff4f2', '⯆']

    return (
        <span
            className={styles.percentChangePill}
            style={{backgroundColor: backgroundColor, color: color,}}
        >
            <small>{symbol}</small> {Math.abs(change)}%
        </span>
    )

}


const RevenueWidget = () => {
    return (
        <div className={styles.revenueWidget}>
            <header className={styles.headerSpacing}>
                <HeaderText>{__('Revenue', 'give')}</HeaderText>
                <HeaderSubText>{__('Show your revenue over time', 'give')}</HeaderSubText>
            </header>
            <RevenueChart />
        </div>
    );
}

const GoalProgressWidget = () => {

    const {campaign} = useCampaignEntityRecord();

    return (
        <div className={styles.progressWidget}>
            <header className={styles.headerSpacing}>
                <HeaderText>{__('Goal progress', 'give')}</HeaderText>
                <HeaderSubText>{__('Show your campaign performance', 'give')}</HeaderSubText>
            </header>
            <GoalProgressChart value={campaign.goalProgress} goal={campaign.goal} />
        </div>
    )
}

const DateRangeFilters = ({options, onSelect, selected}) => {
    return (
        <div className={styles.dateRangeFilter}>
            {options.map((option, index) => (
                <button
                    className={selected === option.value && styles.selectedDateRange}
                    key={index}
                    onClick={() => onSelect(option.value)}
                >
                    {option.label}
                </button>
            ))}
        </div>
    )
}


export default CampaignStats;
