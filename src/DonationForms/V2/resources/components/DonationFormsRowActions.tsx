import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {Interweave} from 'interweave';
import {OnboardingContext} from './Onboarding';
import {UpgradeModalContent} from "./Migration";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

export function DonationFormsRowActions({data, item, removeRow, addRow, setUpdateErrors, parameters}) {
    const {mutate} = useSWRConfig();
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const [OnboardingState, setOnboardingState] = useContext(OnboardingContext);
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
            {__('Really trash the following form?', 'give')}
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
        showConfirmModal(
            __('Upgrade', 'give'),
            UpgradeModalContent,
            async (selected) => {
                const response = await donationFormsApi.fetchWithArgs("/migrate/" + item.id, {}, 'POST');
                await mutate(parameters);
                return response;
            }
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
                    {!item.v3form && (<RowAction
                        onClick={confirmUpgradeModal}
                        actionId={item.id}
                        displayText={__('Upgrade', 'give')}
                        hiddenText={item?.name}
                    />)}
                </>
            )}
        </>
    );
}
