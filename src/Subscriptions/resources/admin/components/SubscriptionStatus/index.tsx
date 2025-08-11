import { SubscriptionStatus } from "../../components/types";
import ReactSelect from 'react-select';
import { __ } from '@wordpress/i18n';
import { stylesConfig } from './stylesConfig';

/**
* @unreleased
*/
type SubscriptionStatusListProps = {
    status: SubscriptionStatus;
    onChange?: (newStatus: SubscriptionStatus) => void;
    isDisabled?: boolean;
    className?: string;
}

/**
* @unreleased
*/
type StatusOption = {
    label: string;
    value: SubscriptionStatus;
    color: string;
};

/**
* @unreleased
*/
export const statusOptions: StatusOption[] = [
    { label: __('Pending', 'give'), value: 'pending', color: '#0b72d9' },
    { label: __('Active', 'give'), value: 'active', color: ' #459948' },
    { label: __('Expired', 'give'), value: 'expired', color: '#d92d0b' },
    { label: __('Completed', 'give'), value: 'completed', color: '#737373' },
    { label: __('Failing', 'give'), value: 'failing', color: '#d92d0b' },
    { label: __('Cancelled', 'give'), value: 'cancelled', color: '#d92d0b' },
    { label: __('Suspended', 'give'), value: 'suspended', color: '#737373' },
    { label: __('Paused', 'give'), value: 'paused', color: '#737373' },
    { label: __('Trashed', 'give'), value: 'trashed', color: '#d92d0b' },
];


/**
* @unreleased
*/
export default function SubscriptionStatusList({ 
    status, 
    onChange, 
    isDisabled = false,
    className = ''
}: SubscriptionStatusListProps) {
    const handleChange = (selectedOption: any) => {
        if (selectedOption && onChange) {
            onChange(selectedOption.value);
        }
    };
    
    return (
        <ReactSelect
            value={statusOptions.find(option => option.value === status)}
            onChange={handleChange}
            options={statusOptions}
            isDisabled={isDisabled}
            className={className}
            classNamePrefix="givewp-select"
            placeholder={__('Select status', 'give')}
            isClearable={false}
            isSearchable={false}
            menuPlacement="auto"
            styles={stylesConfig}
        />
    );
}