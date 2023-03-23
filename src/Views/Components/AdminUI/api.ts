interface PostApiHookResult<T> {
    error?: Error;
    postData: (data: T) => Promise<void>;
}

const usePostRequest = <T>(endpoint: string): PostApiHookResult<T> => {
    const postData = async (postData: T) => {
        try {
            const res = await fetch(endpoint, {
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
