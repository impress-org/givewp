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
    return fetchWithArgs('', {
        page: params.split(',')[0],
        perPage: params.split(',')[1],
        status: params.split(',')[2],
        search: params.split(',')[3]
    });
}
// SWR Fetcher
export const useDonationForms = ({page, perPage, status, search}, swrParams = {}) => {
    return useSWR(keyFunction({page, perPage, status, search}), Fetcher, swrParams);
};

export const keyFunction = ({page, perPage, status, search}) => {
    return `${page},${perPage},${status},${search}`
}
