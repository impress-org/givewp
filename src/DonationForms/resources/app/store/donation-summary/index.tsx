import {createContext, ReactElement, ReactNode, useContext} from 'react';
import {useImmerReducer} from 'use-immer';
import reducer from './reducer';

const StoreContext = createContext(null);
StoreContext.displayName = 'DonationSummaryProvider';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'DonationSummaryDispatch';

export type DonationTotals = {[key: string]: number};
export type DonationSummaryItems = {[key: string]: DonationSummaryLineItem};

/**
 * @since 3.0.0
 */
export type DonationSummaryLineItem = {
    id: string;
    label: string;
    value: string | ReactElement;
    description?: string | ReactElement;
};

type PropTypes = {
    initialState?: {
        items: DonationSummaryItems;
        totals: DonationTotals;
    };
    children: ReactNode;
};

/**
 * @since 3.0.0
 */
const DonationSummaryProvider = ({initialState = {items: {}, totals: {}}, children}: PropTypes) => {
    const [state, dispatch] = useImmerReducer(reducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

const useDonationSummaryContext = () => useContext<PropTypes['initialState']>(StoreContext);
const useDonationSummaryDispatch = () => useContext(StoreContextDispatch);

export {DonationSummaryProvider, useDonationSummaryContext, useDonationSummaryDispatch};
