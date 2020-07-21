// Import vendor dependencies
import { createContext, useContext, useReducer } from 'react';

/**
 * Using existing React Hooks as a lightweight solution for Redux-like state management.
 * The StoreProvider used in the app entry point makes reading and mutating the global data store
 * accessible to any component within the app through the useStoreValue hook.
 *
 * More on this concept: https://dev.to/ramsay/build-a-redux-like-global-store-using-react-hooks-4a7n
 */

export const StoreContext = createContext();

export const StoreProvider = ( { reducer, initialState, children } ) =>(
	<StoreContext.Provider value={ useReducer( reducer, initialState ) }>
		{ children }
	</StoreContext.Provider>
);

export const useStoreValue = () => useContext( StoreContext );
