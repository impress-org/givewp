import {useState} from 'react';

const usePostRequest = (endpoint: string, apiNonce: string, successMessage: string, errorMessage: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const postData = async (postData) => {
        try {
            const res = await fetch(`${endpoint}`, {
                method: 'POST',
                body: JSON.stringify(postData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiNonce,
                },
            });

            if (res.ok) {
                setResult({
                    type: 'success',
                    message: successMessage,
                });
            } else {
                throw new Error(`Failed to post data: ${res.statusText}`);
            }
        } catch (error) {
            setResult({
                type: 'error',
                message: errorMessage,
            });
        }
    };

    return {postData, result};
};

const useGetRequest = (endpoint: string, apiNonce: string, successMessage: string, errorMessage: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const getData = async (getData) => {
        try {
            const res = await fetch(`${endpoint}?${getData}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiNonce,
                },
            });

            if (res.ok) {
                setResult({
                    type: 'success',
                    message: successMessage,
                });

                return res.json();
            } else {
                throw new Error(`Failed to get data: ${res.statusText}`);
            }
        } catch (error) {
            setResult({
                type: 'error',
                message: errorMessage,
            });
        }
    };

    return {getData, result};
};

export {usePostRequest, useGetRequest};
