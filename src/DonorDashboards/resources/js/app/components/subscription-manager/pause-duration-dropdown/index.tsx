import {__} from '@wordpress/i18n';
import {useState} from 'react';

import './style.scss';

type pauseDurationDropDownProps = {
    handlePause :() => void;
}

export default function PauseDurationDropdown({handlePause}: pauseDurationDropDownProps) {
    const [durationOptions, setDurationOptions] = useState(getOptions('months'));

    function getOptions(configType) {
        switch (configType) {
            case 'months':
                return [
                    {value: '1', label: __('1 month', 'give')},
                    {value: '2', label: __('2 months', 'give')},
                    {value: '3', label: __('3 months', 'give')},
                ];
            case 'days':
                return Array.from({length: 30}, (_, i) => ({
                    value: (i + 1).toString(),
                    label: `${i + 1} ${__('days', 'give')}`,
                }));
            case 'weeks':
                return [
                    {value: '1', label: __('1 week', 'give')},
                    {value: '2', label: __('2 weeks', 'give')},
                    {value: '3', label: __('3 weeks', 'give')},
                ];
            default:
                return [];
        }
    }

    return (
        <label className={'give-donor-dashboard__subscription-manager-pause-label'}>
            <span> {__('Duration of pause', 'give')}</span>
            <div className={'give-donor-dashboard__subscription-manager-pause-container'}>
                <select className={'give-donor-dashboard__subscription-manager-pause-select'}>
                    {durationOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m4 6 4 4 4-4"
                        stroke="#0E0E0E"
                        strokeWidth="1.333"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </div>
            <button className={'give-donor-dashboard__subscription-manager-pause-update'}
             onClick={handlePause}>
                {__('Pause Subscription', 'give')}
            </button>
        </label>
    );
}
