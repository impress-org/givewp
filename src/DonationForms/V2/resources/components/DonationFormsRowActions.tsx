import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {Interweave} from 'interweave';
import {UpgradeModalContent} from './Migration';
import {createInterpolateElement} from '@wordpress/element';


const donationFormsApi = new ListTableApi(window.GiveDonationForms);

export function DonationFormsRowActions({data, item, removeRow, addRow, setUpdateErrors, parameters, entity}) {
    const {mutate} = useSWRConfig();
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const trashEnabled = Boolean(data?.trash);
    const deleteEndpoint = trashEnabled && !item.status.includes('trash') ? '/trash' : '/delete';

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteForm = async (selected) => await fetchAndUpdateErrors(parameters, deleteEndpoint, item.id, 'DELETE');

    const confirmDeleteForm = (selected) => (
        <p>
            {__('Really delete the following form?', 'give')}
            <br />
            <Interweave content={item?.title} />
        </p>
    );

    const confirmTrashForm = (selected) => (
        <p>
            {__('Are you sure you want to trash the following donation form? ', 'give')}
            <br />
            <Interweave content={item?.title} />
        </p>
    );

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDeleteForm, deleteForm, 'danger');
    };

    const confirmTrashModal = (event) => {
        showConfirmModal(__('Trash', 'give'), confirmTrashForm, deleteForm, 'danger');
    };

    const confirmUpgradeModal = (event) => {
        showConfirmModal(__('Upgrade', 'give'), UpgradeModalContent, async (selected) => {
            const response = await donationFormsApi.fetchWithArgs('/migrate/' + item.id, {}, 'POST');
            await mutate(parameters);
            return response;
        });
    };

    const urlParams = new URLSearchParams(window.location.search);
    const isCampaignDetailsPage =
        urlParams.get('id') && urlParams.get('page') && 'give-campaigns' === urlParams.get('page');

    const defaultCampaignModalContent = createInterpolateElement(
        __('This will set <title_link/> as the default form for this campaign. Do you want to proceed?', 'give'),
        {
            title_link: <Interweave content={item?.title} />,
        }
    );

    const confirmDefaultCampaignFormModal = (event) => {
        showConfirmModal(
            __('Make as default', 'give'),
            (selected) => <p>{defaultCampaignModalContent}</p>,
            async () => {
                await entity.edit({
                    defaultFormId: item.id
                })

                const response = await entity.save();

                await mutate(parameters);
                return response;
            },
            __('Yes proceed','give')
        );
    };

    return (
        <>
            {parameters.status === 'trash' ? (
                <>
                    <RowAction
                        onClick={removeRow(
                            async () => await fetchAndUpdateErrors(parameters, '/restore', item.id, 'POST')
                        )}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                        hiddenText={item?.name}
                    />
                    <RowAction
                        onClick={confirmModal}
                        actionId={item.id}
                        displayText={__('Delete Permanently', 'give')}
                        hiddenText={item?.name}
                        highlight
                    />
                </>
            ) : (
                <>
                    <RowAction href={item.edit} displayText={__('Edit', 'give')} hiddenText={item?.name} />
                    <RowAction
                        onClick={confirmTrashModal}
                        actionId={item.id}
                        highlight={true}
                        displayText={trashEnabled ? __('Trash', 'give') : __('Delete', 'give')}
                        hiddenText={item?.name}
                    />
                    <RowAction href={item.permalink} displayText={__('View', 'give')} hiddenText={item?.name} />
                    <RowAction
                        onClick={addRow(async (id) => await fetchAndUpdateErrors(parameters, '/duplicate', id, 'POST'))}
                        actionId={item.id}
                        displayText={__('Duplicate', 'give')}
                        hiddenText={item?.name}
                    />
                    {!item.v3form && (
                        <RowAction
                            onClick={confirmUpgradeModal}
                            actionId={item.id}
                            displayText={__('Upgrade', 'give')}
                            hiddenText={item?.name}
                        />
                    )}
                    {isCampaignDetailsPage && !item.isDefaultCampaignForm && (
                        <RowAction
                            onClick={confirmDefaultCampaignFormModal}
                            actionId={item.id}
                            displayText={__('Make as default', 'give')}
                            hiddenText={item?.name}
                        />
                    )}
                </>
            )}
        </>
    );
}
