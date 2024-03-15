import {createContext, ReactNode, useContext} from 'react';
import {CurrencySwitcherSetting} from '@givewp/forms/types';

const StoreContext = createContext(null);
StoreContext.displayName = 'DonationFormSettings';

type PropTypes = {
    value: {
        [key: string]: unknown;
        currencySwitcherSettings?: CurrencySwitcherSetting[];
        currencySwitcherMessage?: string;
        donateButtonCaption: string;
        multiStepNextButtonText: string;
        multiStepFirstButtonText: string;
        showHeader: boolean;
    };
    children: ReactNode;
};

/**
 * @since 3.0.0
 */
const DonationFormSettingsProvider = ({value, children}: PropTypes) => {
    return <StoreContext.Provider value={value}>{children}</StoreContext.Provider>;
};

const useDonationFormSettings = () => useContext<PropTypes['value']>(StoreContext);

export {DonationFormSettingsProvider, useDonationFormSettings};
