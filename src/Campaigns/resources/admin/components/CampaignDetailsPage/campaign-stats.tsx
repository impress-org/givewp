import {__} from '@wordpress/i18n';
import {useEffect, useState} from "react";
import CampaignsApi from "../api";
import {getGiveCampaignDetailsWindowData} from "./index";

const {adminUrl, campaign, apiRoot, apiNonce} = getGiveCampaignDetailsWindowData();
const API = new CampaignsApi({apiNonce, apiRoot});

const pluck = (array: any[], property: string) => array.map(element => element[property])

const filterOptions = [
    { label: __('Today'), value: 1, description: __('from today') },
    { label: __('Last 7 days'), value: 7, description: __('from the last 7 days') },
    { label: __('Last 30 days'), value: 30, description: __('from the last 30 days') },
    { label: __('Last 90 days'), value: 90, description: __('from the last 90 days') },
    { label: __('All-time'), value: 0, description: '' }, // Note: description intentionally left empty.
]

const CampaignStats = ({ campaign }) => {

    const [dayRange, setDayRange] = useState(null);
    const [stats, setStats] = useState([]);

    useEffect(() => {
        onDayRangeChange(0)
    }, [])

    const onDayRangeChange = async (days: number) => {
        setDayRange(days)
        const response = await API.fetchWithArgs(`/${campaign.properties.id}/stats`, {
            rangeInDays: days,
        }, 'GET')
        setStats(response)
    }

    const widgetDescription = filterOptions.find(option => option.value === dayRange)?.description

    return (
        <>
            <DateRangeFilters selected={dayRange} options={filterOptions} onSelect={onDayRangeChange} />

            <div style={{
                display: 'flex',
                flexDirection: 'row',
                gap: '20px',
                padding: '1rem',
            }}>
                <StatWidget label={__('Amount Raised')} values={pluck(stats, 'amountRaised')} description={widgetDescription} />
                <StatWidget label={__('Donation Count')} values={pluck(stats, 'donationCount')} description={widgetDescription} />
                <StatWidget label={__('Donor Count')} values={pluck(stats, 'donorCount')} description={widgetDescription} />
            </div>
        </>
    )
}

const StatWidget = ({label, values, description}) => {
    return (
        <div style={{
            flex: 1,
            padding: '20px 10px 10px',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'space-between',
            backgroundColor: 'white',
            borderRadius: '5px',
            gap: '10px',
        }}>
            <header style={{flex: '1'}}>
                <div>{label}</div>
            </header>
            <div style={{
                flex: '1',
                padding: '5px',
                display: 'flex',
                flexDirection: 'row',
                justifyContent: 'space-between',
                alignItems: 'baseline'
            }}>
                <span style={{fontSize: '2em'}}>{values[0]}</span>
                {!! values[1] && (
                    <PercentChangePill value={values[0]} comparison={values[1]} />
                )}
            </div>
            <footer style={{flex: '1'}}>
                <small>{description}</small>
            </footer>
        </div>
    )
}

const PercentChangePill = ({value, comparison}) => {

    const change = Math.round(100 * ((value - comparison) / comparison)) ?? 0

    console.log(value, comparison, change)

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

const DateRangeFilters = ({options, onSelect, selected}) => {
    return (
        <div style={{
            padding: '1rem',
            backgroundColor: '#f3f4f6',
            display: 'flex',
            flexDirection: 'row',
            justifyContent: 'end',
            gap: '1rem',
        }}>
            {options.map((option, index) => (
                <button
                    key={index}
                    style={{
                        border: 0,
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

export default CampaignStats;
