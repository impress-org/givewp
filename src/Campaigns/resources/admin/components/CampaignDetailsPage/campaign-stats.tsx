import {__} from '@wordpress/i18n';
import {useEffect, useState} from "react";
import CampaignsApi from "../api";
import {getGiveCampaignDetailsWindowData} from "./index";
import {setDates} from "../../../../../../assets/src/js/admin/reports/store/actions";
import amount from "@givewp/form-builder/blocks/fields/amount";

interface DateRangeOption {
    label: string;
    value: number;
}

const {adminUrl, campaign, apiRoot, apiNonce} = getGiveCampaignDetailsWindowData();
const API = new CampaignsApi({apiNonce, apiRoot});

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

    const pluck = property => element => element[property]

    const amountRaised = stats.map(pluck('amountRaised'))
    const donationCount = stats.map(pluck('donationCount'))
    const donorCount = stats.map(pluck('donorCount'))

    console.log(amountRaised)

    return (
        <>
            <DateRangeFilters selected={dayRange} onSelect={onDayRangeChange} options={[
                { label: __('Today'), value: 1 },
                { label: __('Last 7 days'), value: 7 },
                { label: __('Last 30 days'), value: 30 },
                { label: __('Last 90 days'), value: 90 },
                { label: __('All-time'), value: 0 },
            ]} />

            <div style={{
                display: 'flex',
                flexDirection: 'row',
                gap: '20px',
                padding: '1rem',
            }}>
                <StatWidget label={__('Amount Raised')} values={amountRaised} />
                <StatWidget label={__('Donation Count')} values={donationCount} />
                <StatWidget label={__('Donor Count')} values={donorCount} />
            </div>
        </>
    )
}

const StatWidget = ({label, values}) => {
values[1] = 10
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
                    <span>
                        {Math.round(100 * Math.abs( (values[1] - values[0]) / values[0] )) / 10 ?? 0}%

                    </span>
                )}
            </div>
            <footer style={{flex: '1'}}>
                {/*{!! values[1] && <small>from last X days</small>}*/}
            </footer>
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
