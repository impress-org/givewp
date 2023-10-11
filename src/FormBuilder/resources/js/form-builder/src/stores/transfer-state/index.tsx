import {createContext, ReactNode, useContext, useReducer} from 'react';
import reducer, {setTransferState} from './reducer';
import {TransferState} from './transferState';

const StoreContext = createContext(null);
StoreContext.displayName = 'TransferState';

const StoreContextDispatch = createContext(null);
StoreContextDispatch.displayName = 'TransferStateDispatch';

/**
 * @unreleased
 */
const TransferStateProvider = ({initialState, children}: { initialState: TransferState; children: ReactNode }) => {
    const [state, dispatch] = useReducer(reducer, initialState);

    return (
        <StoreContext.Provider value={state}>
            <StoreContextDispatch.Provider value={dispatch}>{children}</StoreContextDispatch.Provider>
        </StoreContext.Provider>
    );
};

const useTransferState = () => useContext<TransferState>(StoreContext);

const useTransferStateDispatch = () => useContext(StoreContextDispatch);

export {TransferStateProvider, useTransferState, useTransferStateDispatch, setTransferState};
