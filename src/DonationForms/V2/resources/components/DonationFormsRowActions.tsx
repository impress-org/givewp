import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {MigrationOnboardingContext} from './DonationFormsListTable';
import {Interweave} from 'interweave';

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

export function DonationFormsRowActions({data, item, removeRow, addRow, setUpdateErrors, parameters}) {
    const {mutate} = useSWRConfig();
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const [onboardingState, setOnboardingState] = useContext(MigrationOnboardingContext);
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

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDeleteForm, deleteForm, 'danger');
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
                        onClick={trashEnabled ? removeRow(deleteForm) : confirmModal}
                        actionId={item.id}
                        highlight={!trashEnabled}
                        displayText={trashEnabled ? __('Trash', 'give') : __('Delete', 'give')}
                        hiddenText={item?.name}
                    />
                    <RowAction href={item.permalink} displayText={__('View', 'give')} hiddenText={item?.name} />
                    {item.migrate && (
                        <RowAction
                            onClick={addRow(async (id) => {
                                const response = await fetchAndUpdateErrors(parameters, '/duplicate', id, 'POST');

                                if (!onboardingState.migrationOnboardingCompleted) {
                                    setOnboardingState(prev => ({
                                        ...prev,
                                        showMigrationSuccessDialog: true,
                                        formId: response.successes[0]
                                    }))
                                }

                                return response
                            })}
                            actionId={item.id}
                            displayText={__('Migrate', 'give')}
                            hiddenText={item?.name}
                        />
                    )}
                    {item.transfer && (
                        <RowAction
                            onClick={() => {
                                if (!onboardingState.transferOnboardingCompleted) {
                                    setOnboardingState(prev => ({
                                        ...prev,
                                        showTransferSuccessDialog: true,
                                        formName: item?.name,
                                    }))
                                } else {
                                    addRow(async (id) => await fetchAndUpdateErrors(parameters, '/duplicate', id, 'POST'));
                                }
                            }}
                            actionId={item.id}
                            displayText={__('Transfer', 'give')}
                            hiddenText={item?.name}
                        />
                    )}
                    <RowAction
                        onClick={addRow(async (id) => await fetchAndUpdateErrors(parameters, '/duplicate', id, 'POST'))}
                        actionId={item.id}
                        displayText={__('Duplicate', 'give')}
                        hiddenText={item?.name}
                    />
                </>
            )}
        </>
    );
}
