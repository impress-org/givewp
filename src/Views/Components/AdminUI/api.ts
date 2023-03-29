import {useState} from 'react';
import {format} from 'date-fns';

const PostRequest = (endpoint: string, apiNonce: string, successMessage: string, errorMessage: string) => {
    const [result, setResult] = useState({type: null, message: ''});

    const postData = async (postData) => {
        postData.createdAt = format(postData.createdAt, 'yyyy-MM-dd HH:mm:ss');

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
export {PostRequest};
