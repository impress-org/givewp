import {__} from '@wordpress/i18n';
import {useEffect, useState} from "react";
import RevenueChart from "./RevenueChart";
import GoalProgressChart from "./GoalProgressChart";
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const campaignId = new URLSearchParams(window.location.search).get('id');

const pluck = (array: any[], property: string) => array.map(element => element[property])

const filterOptions = [
    { label: __('Today'), value: 1, description: __('from today') },
    { label: __('Last 7 days'), value: 7, description: __('from the last 7 days') },
    { label: __('Last 30 days'), value: 30, description: __('from the last 30 days') },
    { label: __('Last 90 days'), value: 90, description: __('from the last 90 days') },
    { label: __('All-time'), value: 0, description: __('total for all-time') },
]

const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
})

const CampaignStats = () => {

    const [dayRange, setDayRange] = useState(null);
    const [stats, setStats] = useState([]);

    useEffect(() => {
        onDayRangeChange(0)
    }, [])

    const onDayRangeChange = async (days: number) => {
        setDayRange(days)

        apiFetch({path: addQueryArgs( '/give-api/v2/campaigns/' + campaignId +'/statistics', {rangeInDays: days} ) } )
            .then(setStats);
    }

    const widgetDescription = filterOptions.find(option => option.value === dayRange)?.description

    return (
        <>
            <DateRangeFilters selected={dayRange} options={filterOptions} onSelect={onDayRangeChange} />

            <Row>
                <StatWidget label={__('Amount Raised')} values={[5000, 3000]} description={widgetDescription} formatter={currency} />
                <StatWidget label={__('Donation Count')} values={[200, 300]} description={widgetDescription} />
                <StatWidget label={__('Donor Count')} values={[100, 100]} description={widgetDescription} />
            </Row>

            <Row>
                <RevenueWidget />
                <GoalProgressWidget />
            </Row>
        </>
    )
}

const HeaderText = ({children}) => {
    return (
        <div style={{
            fontSize: '16px',
            fontWeight: 600,
            lineHeight: '24px',
        }}>
            {children}
        </div>
    )
}

const HeaderSubText = ({children}) => {
    return (
        <div style={{
            fontSize: '14px',
            fontWeight: 400,
            lineHeight: '20px',
            color: '#4B5563',
        }}>
            {children}
        </div>
    )
}

const FooterText = ({children}) => {
    return (
        <div style={{
            fontSize: '12px',
            fontWeight: 400,
            lineHeight: '18px',
            color: '#1F2937',
        }}>
            {children}
        </div>
    )
}

const DisplayText = ({children}) => {
    return (
        <div style={{
            fontSize: '36px',
            fontWeight: 600,
            lineHeight: '43.57px',
            color: '#060C1A',
        }}>
            {children}
        </div>
    )
}

const StatWidget = ({label, values, description, formatter = null}) => {
    return (
        <div style={{
            flex: 1,
            padding: '24px',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'space-between',
            backgroundColor: 'white',
            borderRadius: '8px',
            gap: '10px',
        }}>
            <header style={{flex: '1'}}>
                <HeaderText>{label}</HeaderText>
            </header>
            <div style={{
                flex: '1',
                padding: '5px',
                display: 'flex',
                flexDirection: 'row',
                justifyContent: 'space-between',
                alignItems: 'baseline'
            }}>
                <DisplayText>
                    {formatter?.format(values[0]) ?? values[0]}
                </DisplayText>
                {!! values[1] && (
                    <PercentChangePill value={values[0]} comparison={values[1]} />
                )}
            </div>
            <footer style={{flex: '1'}}>
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
        <span style={{
            padding: '0.5rem',
            fontSize: '.8rem',
            borderRadius: '1rem',
            backgroundColor: backgroundColor,
            color: color,
        }}>
            <small>{symbol}</small> {Math.abs(change)}%
        </span>
    )

}

const RevenueWidget = () => {
    return (
        <div style={{
            flex: 2,
            backgroundColor: 'white',
            padding: '20px',
            borderRadius: '8px',
        }}>
            <header>
                <HeaderText>Revenue</HeaderText>
                <HeaderSubText>{__('Show your revenue over time')}</HeaderSubText>
            </header>
            <RevenueChart />
        </div>
    );
}

const GoalProgressWidget = () => {
    return (
        <div style={{
            flex: 1,
            backgroundColor: 'white',
            padding: '20px',
            borderRadius: '8px',
        }}>
            <HeaderText>{__('Goal Progress')}</HeaderText>
            <HeaderSubText>{__('Show your campaign performance')}</HeaderSubText>
            <GoalProgressChart value={450} goal={2000} />
        </div>
    )
}

const DateRangeFilters = ({options, onSelect, selected}) => {
    return (
        <div style={{
            padding: '1rem',
            backgroundColor: '#f3f4f6',
            display: 'flex',
            flexDirection: 'row',
            justifyContent: 'end',
            gap: '1rem',
            borderRadius: '8px',
        }}>
            {options.map((option, index) => (
                <button
                    key={index}
                    style={{
                        border: 0,
                        cursor: 'pointer',
                        padding: '0.5rem 1rem',
                        backgroundColor: selected === option.value ? 'white' : 'transparent',
                    }}
                    onClick={() => onSelect(option.value)}
                >
                    {option.label}
                </button>
            ))}
        </div>
    )
}

const Row = ({children}) => (
    <div style={{
        display: 'flex',
        flexDirection: 'row',
        gap: '20px',
        padding: '1rem',
    }}>
        {children}
    </div>
)

export default CampaignStats;
