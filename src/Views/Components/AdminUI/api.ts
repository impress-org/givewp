import {useState} from 'react';

const usePostRequest = (endpoint: string, apiNonce: string, successMessage: string, errorMessage: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const postData = async (data) => {
        try {
            const res = await fetch(`${endpoint}`, {
                method: 'POST',
                body: JSON.stringify(data),
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

const useGetRequest = (endpoint: string, apiNonce: string, successMessage?: string, errorMessage?: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const getData = async (data) => {
        try {
            const res = await fetch(`${endpoint}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiNonce,
                },
                body: JSON.stringify(data),
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

const usePatchRequest = (endpoint: string, apiNonce: string, successMessage?: string, errorMessage?: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const patchData = async (data) => {
        try {
            const res = await fetch(`${endpoint}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiNonce,
                },
                body: JSON.stringify(data),
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

    return {patchData, result};
};

const useDeleteRequest = (endpoint: string, apiNonce: string, successMessage?: string, errorMessage?: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const deleteData = async (data) => {
        try {
            const res = await fetch(`${endpoint}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': apiNonce,
                },
                body: JSON.stringify(data),
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

    return {deleteData, result};
};
export {usePostRequest, useGetRequest, usePatchRequest, useDeleteRequest};
