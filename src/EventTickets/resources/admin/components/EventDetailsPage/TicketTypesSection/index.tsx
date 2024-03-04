import {__} from '@wordpress/i18n';
import cx from 'classnames';
import {ListTable} from '@givewp/components';
import A11yDialogInstance from 'a11y-dialog';
import styles from './TicketTypesSection.module.scss';
import ListTableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {ApiSettingsProps} from '../types';
import {createContext, useRef, useState} from 'react';
import {TicketTypesRowActions} from './TicketTypesRowActions';
import ListTableApi from '@givewp/components/ListTable/api';
import {useResetPage} from '@givewp/components/ListTable/hooks/useResetPage';
import {A11yDialog} from 'react-a11y-dialog';
// import CreateEventModal from '../CreateEventModal';

export const ShowConfirmModalContext = createContext((label, confirm, action, type = null) => {});

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTicketsDetails.pluginUrl}/assets/dist/images/list-table/blank-slate-event-tickets-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No ticket created yet', 'give')} />
            <h3>{__('No ticket created yet', 'give')}</h3>
            <p className={styles.helpMessage}>{__('Create a ticket to complete your event setup.', 'give')}</p>
            <p>
                {/*Todo: Set an onClick event to open the CreateTicketTypeModal*/}
                <a
                    href={`${window.GiveEventTicketsDetails.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets&new=event`}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Create ticket', 'give')}
                </a>
            </p>
        </div>
    );
};

export default function EventTicketsListTable() {
    const [page, setPage] = useState<number>(1);
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectedNames, setSelectedNames] = useState([]);
    const [modalContent, setModalContent] = useState<{
        confirm;
        action;
        label;
        type?: 'normal' | 'warning' | 'danger';
    }>({
        confirm: (selected) => {},
        action: (selected) => {},
        label: '',
    });
    const dialog = useRef() as {current: A11yDialogInstance};
    const [sortField, setSortField] = useState<{sortColumn: string; sortDirection: string}>({
        sortColumn: 'id',
        sortDirection: 'asc',
    });
    const {sortColumn, sortDirection} = sortField;

    const parameters = {
        page,
        perPage: 30,
        sortColumn,
        sortDirection,
        locale: navigator.language,
        testMode: undefined,
    };

    const apiSettings: ApiSettingsProps = {
        ...window.GiveEventTicketsDetails,
        table: window.GiveEventTicketsDetails.ticketTypesTable,
    };
    apiSettings.apiRoot += `/event/${apiSettings.event.id}/ticket-types/list-table`;

    const archiveApi = useRef(new ListTableApi(apiSettings)).current;

    const {data, error, isValidating, mutate} = archiveApi.useListTable(parameters);

    useResetPage(data, page, setPage, {});

    const showConfirmActionModal = (label, confirm, action, type: 'normal' | 'warning' | 'danger' | null = null) => {
        setModalContent({confirm, action, label, type});
        dialog.current.show();
    };

    const setSortDirectionForColumn = (column, direction) => {
        setSortField((previousState) => {
            return {
                ...previousState,
                sortColumn: column,
                sortDirection: direction,
            };
        });
    };

    return (
        <section className={styles.ticketTypesSection}>
            <h2>{__('Tickets', 'give')}</h2>
            <ShowConfirmModalContext.Provider value={showConfirmActionModal}>
                <ListTable
                    apiSettings={apiSettings}
                    sortField={sortField}
                    setSortDirectionForColumn={setSortDirectionForColumn}
                    singleName={__('Ticket', 'give')}
                    pluralName={__('Tickets', 'give')}
                    title={__('Tickets', 'give')}
                    rowActions={TicketTypesRowActions}
                    parameters={{}}
                    data={data}
                    error={error}
                    isLoading={isValidating}
                    align={'start'}
                    testMode={undefined}
                    listTableBlankSlate={ListTableBlankSlate()}
                    columnFilters={[]}
                />
            </ShowConfirmModalContext.Provider>

            <A11yDialog
                id="giveListTableModal"
                dialogRef={(instance) => (dialog.current = instance)}
                title={modalContent.label}
                titleId={ListTableStyles.modalTitle}
                classNames={{
                    container: ListTableStyles.container,
                    overlay: ListTableStyles.overlay,
                    dialog: cx(ListTableStyles.dialog, {
                        [ListTableStyles.warning]: modalContent?.type === 'warning',
                        [ListTableStyles.danger]: modalContent?.type === 'danger',
                    }),
                    closeButton: 'hidden',
                }}
            >
                <div className={ListTableStyles.modalContent}>
                    {modalContent?.confirm(selectedIds, selectedNames) || null}
                </div>
                <div className={ListTableStyles.gutter}>
                    <button id={ListTableStyles.cancel} onClick={(event) => dialog.current?.hide()}>
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        id={ListTableStyles.confirm}
                        onClick={async (event) => {
                            dialog.current?.hide();
                            await modalContent.action(selectedIds);
                            await mutate();
                        }}
                    >
                        {__('Confirm', 'give')}
                    </button>
                </div>
            </A11yDialog>
        </section>
    );
}

