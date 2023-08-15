import {useDispatch, useSelect} from '@wordpress/data';
import {store as noticesStore} from '@wordpress/notices';
import {filter} from 'lodash';
import {SnackbarList} from '@wordpress/components';

export default function NoticesContainer() {
    const notices = useSelect<any>((select) => select(noticesStore).getNotices(), []);
    const {removeNotice} = useDispatch(noticesStore);
    const snackbarNotices = filter(notices, {
        type: 'snackbar',
    });

    return (
        <SnackbarList
            notices={snackbarNotices}
            className="components-editor-notices__snackbar"
            // @ts-ignore
            onRemove={removeNotice}
        />
    );
}