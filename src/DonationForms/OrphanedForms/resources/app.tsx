import {__, _x} from '@wordpress/i18n';
import {useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {Button, Card, Modal, Notice, Pagination as ListTablePagination, Spinner, Table} from '@givewp/components';
import {useForms} from '../../resources/utils';
import useCampaigns from '../../../Campaigns/Blocks/shared/hooks/useCampaigns';
import {format} from 'date-fns';
import * as locales from 'date-fns/locale';
import styles from './styles.module.scss';


const browserLanguage = navigator.language;
const localizedCode = browserLanguage.replace('-', '');
const genericCode = browserLanguage.split('-')[0];
const locale = locales[localizedCode] ?? locales[genericCode] ?? locales.enUS;
const dateFormat = _x('MM/dd/yyyy \'at\' h:mmaaa', 'Date format', 'give');

type Props = {
    ids: number[];
    currentPage: number;
    showModal: boolean;
    campaign?: number;
}

const OrphanedFormsListTable = () => {
    const [state, setState] = useState<Props>({
        ids: [],
        currentPage: 1,
        showModal: false,
    });

    const {forms, hasResolved, totalPages} = useForms({
        page: state.currentPage,
        status: ['orphaned'],
    });

    const {campaigns, hasResolved: campaignsLoaded} = useCampaigns();

    const showModal = (showModal: boolean) => {
        setState(prevState => {
            return {
                ...prevState,
                showModal,
            };
        });
    };

    const selectForm = (selectedId: number) => {

        let ids = state.ids;

        if (ids.includes(selectedId)) {
            ids = ids.filter(id => id !== selectedId);
        } else {
            ids.push(selectedId);
        }

        setState(prevState => {
            return {
                ...prevState,
                ids,
            };
        });
    };

    const selectCampaign = (campaign: number) => {
        setState(prevState => {
            return {
                ...prevState,
                campaign,
            };
        });
    };


    const setCurrentPage = (currentPage: number) => {
        setState((previousState) => {
            return {
                ...previousState,
                currentPage,
            };
        });
    };

    const getFormName = (id: number) => {
        //@ts-ignore
        const form = forms.filter(form => form.id === id);
        //@ts-ignore
        return form[0].title;
    };

    const handleSave = () => {
        apiFetch({
            path: '/givewp/v3/associate-forms-with-campaign',
            method: 'POST',
            data: {
                campaignId: state.campaign,
                formIDs: state.ids,
            },
        }).then(() => {
            window.location.reload();
        });
    };

    const Pagination = () => (
        <div className={styles.pagination}>
            <ListTablePagination
                currentPage={state.currentPage}
                setPage={(page: number) => setCurrentPage(page)}
                totalPages={totalPages}
                disabled={!hasResolved}
            />
        </div>
    );


    const ConfirmationModal = () => {
        return (
            <Modal
                type="info"
                handleClose={() => showModal(false)}
            >
                <Modal.Title>
                    {__('Associate forms with campaign', 'give')}

                    <Modal.CloseIcon onClick={() => showModal(false)} />
                </Modal.Title>

                <div>
                    <h4>{__('Selected forms', 'give')}</h4>
                    {state.ids.map(id => (
                        <p key={id}>
                            {getFormName(id)}
                        </p>
                    ))}
                </div>

                <div className={styles.section}>
                    <h4>{__('Select campaign', 'give')}</h4>
                    <select
                        className={styles.input}
                        onChange={e => selectCampaign(Number(e.target.value))}>
                        <option>
                            {__('Select campaign', 'give')}
                        </option>
                        {campaigns.map((campaign) => (
                            <option
                                selected={state.campaign === campaign.id}
                                value={campaign.id}
                            >
                                {campaign.title}
                            </option>
                        ))}
                    </select>

                </div>

                <div className={styles.buttons}>

                    <Button
                        className="button button-primary"
                        onClick={handleSave}
                        icon={null}
                        disabled={!state.campaign}
                    >
                        {__('Associate forms ', 'give')}
                    </Button>

                    <Button
                        className="button button-secondary"
                        icon={null}
                        type="reset"
                        onClick={() => showModal(false)}
                    >
                        {__('Cancel', 'give')}
                    </Button>
                </div>
            </Modal>
        );
    };


    const columns = [
        {
            key: 'id',
            label: __('Donation Form', 'give'),
        },
        {
            key: 'createdAt',
            label: __('Date created', 'give'),
        },
    ];

    const columnFilters = {
        id: (id: number, form: {title: string}) => (
            <div className={styles.row}>
                <input type="checkbox" onChange={() => selectForm(id)} />
                <div>{form.title}</div>
            </div>
        ),
        createdAt: (value: {date: string}) => {
            return format(new Date(value.date), dateFormat, {locale});
        },
    };

    if (!hasResolved) {
        return (
            <Notice>
                <Spinner />
                <h2>{__('Loading donation forms', 'give')}</h2>
            </Notice>
        );
    }

    return (
        <>
            <Card>
                <Table
                    columns={columns}
                    columnFilters={columnFilters}
                    data={forms ?? []}
                    isLoading={!hasResolved}
                    stripped={false}
                    isSorting={false}
                />
            </Card>

            <div className={styles.footerRow}>
                {hasResolved && forms && (
                    <button
                        className="button"
                        disabled={state.ids.length === 0}
                        onClick={() => showModal(true)}
                    >
                        {__('Associate forms', 'give')}
                    </button>
                )}
                <Pagination />
            </div>
            {state.showModal && campaignsLoaded && <ConfirmationModal />}
        </>
    );
};

export default OrphanedFormsListTable;
