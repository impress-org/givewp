const PostRequest = (endpoint: string, apiNonce: string) => {
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

            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
        } catch (error) {
            throw new Error(`Unable to post data: ${error.message}`);
        }
    };

    return {postData};
};

export {PostRequest};
