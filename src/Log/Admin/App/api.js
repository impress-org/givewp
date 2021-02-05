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
export const Fetcher = ( endpoint ) => API.get( endpoint ).then( ( res ) => {
	const { data, ...rest } = res.data;
	return {
		data,
		response: rest,
	};
} );

export const useLogFetcher = ( endpoint, params = {} ) => {
	const { data, error } = useSWR( endpoint, Fetcher, params );
	return {
		data: data ? data.data : undefined,
		isLoading: ! error && ! data,
		isError: error,
		response: data ? data.response : undefined,
	};
};

