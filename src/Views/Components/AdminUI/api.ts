const usePostRequest = (endpoint: string) => {
    const postData = async (postData, params = {}) => {
        try {
            const urlParams = new URLSearchParams(params);
            const res = await fetch(`${endpoint}?${urlParams.toString()}`, {
                method: 'POST',
                body: JSON.stringify(postData),
                headers: {
                    'Content-Type': 'application/json',
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

export {usePostRequest};
