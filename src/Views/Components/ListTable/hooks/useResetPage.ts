import {useEffect} from 'react';

export const useResetPage = (data, page, setPage, filters) => {
    //if we're displaying a non-existent page (like after deleting an item), go to the last available page
    useEffect(() => {
        if (data?.totalPages && page > data.totalPages) {
            setPage(data.totalPages);
        }
    }, [data]);

    //go back to the first page whenever filters change
    useEffect(() => {
        setPage(1);
    }, [filters]);
};
