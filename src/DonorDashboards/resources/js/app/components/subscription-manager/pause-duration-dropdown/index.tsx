import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import './style.scss';

type PauseDurationDropDownProps = {
    handlePause: (pauseDuration: number) => void;
    closeModal: () => void;
};

type durationOptions = { value: string; label: string }[];

const durationOptions: durationOptions = [
    { value: '1', label: __('1 month', 'give') },
    { value: '2', label: __('2 months', 'give') },
    { value: '3', label: __('3 months', 'give') },
];

export default function PauseDurationDropdown({handlePause, closeModal}: PauseDurationDropDownProps) {
    const [pauseDuration, setPauseDuration] = useState<number>(1);

    const updateSubscription = () => {
        closeModal();
        handlePause(pauseDuration);
    };

    return (
        <label className="give-donor-dashboard__subscription-manager-pause-label">
            <span>{__('Duration of pause', 'give')}</span>
            <div className="give-donor-dashboard__subscription-manager-pause-container">
                <select
                    className="give-donor-dashboard__subscription-manager-pause-select"
                    onChange={(e) => setPauseDuration(parseInt(e.target.value))}
                >
                    <option value="0" disabled>
                        {__('How long would you like to pause your subscription?', 'give')}
                    </option>
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
            <button className="give-donor-dashboard__subscription-manager-pause-update" onClick={updateSubscription}>
                {__('Pause Subscription', 'give')}
            </button>
        </label>
    );
}
