import {useContext} from 'react';
import {useSWRConfig} from 'swr';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import ListTableApi from '@givewp/components/ListTable/api';
import {getCampaignOptionsWindowData} from '@givewp/campaigns/utils';

const API = new ListTableApi(getCampaignOptionsWindowData());

export function CampaignsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const duplicateItem = async () => {
        const response = await API.fetchWithArgs(`/${item.id}/duplicate`, {}, 'POST');
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const confirmDuplicate = () => (
        <p>{sprintf(__('Are you sure you want to duplicate the following campaign: %s?', 'give'), item.titleRaw)}</p>
    );

    const confirmModal = () => showConfirmModal(__('Duplicate', 'give'), confirmDuplicate, duplicateItem, 'info');

    return (
        <>
            <RowAction
                href={`edit.php?post_type=give_forms&page=give-campaigns&id=${item.id}`}
                displayText={__('Edit', 'give')}
            />
            <RowAction
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                onClick={confirmModal}
            />
        </>
    );
}
