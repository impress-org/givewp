import {__} from '@wordpress/i18n';
import {useState} from 'react';
import {Card, Label, Modal, Notice, Pagination as ListTablePagination, Spinner, Table} from '@givewp/components';
import {useForms} from '../../resources/utils';

import styles from './styles.module.scss';

type Props = {
    ids: number[];
    currentPage: number;
    showModal: boolean;
}

const OrphanedFormsListTable = () => {
    const [state, setState] = useState<Props>({
        ids: [],
        currentPage: 1,
        showModal: false,
    });

    const {forms, hasResolved, totalPages} = useForms({
        ids: state.ids,
        page: state.currentPage,
        status: ['orphaned']
    });

    if (!hasResolved) {
        return null;
    }

    console.log({forms})

    const showModal = (showModal: boolean) => {
        setState(prevState => {
            return {
                ...prevState,
                showModal,
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

    const Pagination = () => (
        <div className={styles.pagination}>
            <ListTablePagination
                currentPage={state.currentPage}
                setPage={() => setCurrentPage(state.currentPage)}
                totalPages={totalPages}
                disabled={!hasResolved}
            />
        </div>
    )


    const getModal = () => {
        return (
            <Modal
                visible={state.showModal}
                type="warning"
                handleClose={() => showModal(false)}
            >
                <Modal.Title>
                    <Label type="warning" text={__('Associate Donation Forms', 'give')} />

                    <strong style={{marginLeft: 20}}>
                        {__('ID', 'give')}
                    </strong>

                    <Modal.CloseIcon onClick={() => showModal(false)} />
                </Modal.Title>

                <Modal.Section title={__('Description', 'give')} content="Content" />
            </Modal>
        );
    };


    const columns = [
        {
            key: 'ID',
            label: __('ID', 'give'),
        },
        {
            key: 'title',
            label: __('Donation Form', 'give'),
        },
    ];

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
            <div className={styles.headerRow}>
                <Pagination />
            </div>

            <Card>
                <Table
                    columns={columns}
                    data={forms ?? []}
                    isLoading={!hasResolved}
                    stripped={false}
                    isSorting={false}
                />
            </Card>

            <div className={styles.footerRow}>
                <Pagination />
            </div>
        </>
    );
};

export default OrphanedFormsListTable;
