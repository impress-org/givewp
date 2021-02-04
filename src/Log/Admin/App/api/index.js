import axios from 'axios';
import useSWR from 'swr';

const API = axios.create( {
	baseURL: window.wpApiSettings.root + 'give-api/v2/logs',
	headers: {
		'Content-Type': 'application/json',
		'X-WP-Nonce': window.wpApiSettings.nonce,
	},
} );

export default API;

export const CancelToken = axios.CancelToken.source();

// SWR Fetcher
export const Fetcher = ( endpoint ) => API.get( endpoint ).then( res => res.data.data );

export const useLogFetch = ( endpoint ) => {
	const { data, error } = useSWR( endpoint, Fetcher );

	return {
		data,
		isLoading: ! error && ! data,
		isError: error,
	};
};

