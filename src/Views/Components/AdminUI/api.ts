import useSWR from 'swr';

interface ApiHookResult<T> {
    error?: Error;
    postData: (data: T) => Promise<void>;
}

const useApi = <T>(endpoint: string): ApiHookResult<T> => {
    const { error, mutate } = useSWR(endpoint);

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

            await mutate();
        } catch (error) {
            throw new Error(`Unable to post data: ${error.message}`);
        }
    };

    return { error, postData };
};

export { useApi };
