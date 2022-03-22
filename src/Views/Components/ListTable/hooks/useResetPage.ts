import {useEffect} from "react";

export const useResetPage = (data, page, setPage) => {
    //if we're displaying a non-existent page (like after deleting an item), go to the last available page
    useEffect(() => {
        if(data?.totalPages && page > data.totalPages){
            setPage(data.totalPages);
        }
    }, [data]);
}
