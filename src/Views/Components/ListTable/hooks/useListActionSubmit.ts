import {useState} from 'react';
import type {ListActionContext} from '../ListTablePage/types';
import {runListAction, RunListActionArgs} from '../actions/runListAction';

/**
 * For a confirm modal or dialog: runs the action with `runListAction` and says whether it is still
 * running, so the confirm button can show it is busy.
 *
 * @since TBD
 */
export default function useListActionSubmit(context: ListActionContext) {
    const [isBusy, setIsBusy] = useState(false);

    const submit = async (args: RunListActionArgs) => {
        setIsBusy(true);
        await runListAction(args, context);
    };

    return {isBusy, submit};
}
