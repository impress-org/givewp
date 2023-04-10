import {createContext, ReactNode, useContext} from 'react';
import type {Gateway} from '@givewp/forms/types';

const GiveDonationFormStore = createContext(null);
GiveDonationFormStore.displayName = 'GiveDonationFormStore';

const useGiveDonationFormStore = () => useContext(GiveDonationFormStore);

type PropTypes = {
    initialState: {
        gateways: Gateway[];
    };
    children: ReactNode;
};

const GiveDonationFormStoreProvider = ({initialState, children}: PropTypes) => (
    <GiveDonationFormStore.Provider value={initialState}>{children}</GiveDonationFormStore.Provider>
);

export {GiveDonationFormStoreProvider, useGiveDonationFormStore};
