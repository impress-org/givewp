import useSWR from 'swr';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const headers = {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveDonationForms.apiNonce,
};

export const fetchWithArgs = (endpoint, args, method = 'GET') => {
    const url = new URL(window.GiveDonationForms.apiRoot + endpoint);
    for (const [param, value] of Object.entries(args)) {
        url.searchParams.set(param, value as string);
    }
    return fetch(url.href, {
        method: method,
        headers: headers,
    }).then((res) => {
        if(!res.ok){
            throw new Error();
        }
        return res.json();
    });
}


const Fetcher = (params) => {
    return fetchWithArgs('', params);
}
// SWR Fetcher
export function useDonationForms({page, perPage, status, search}) {
    const {data, error} = useSWR({page, perPage, status, search}, Fetcher);

    return {data, error};
}
