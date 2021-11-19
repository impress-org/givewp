import {useState} from 'react';
import {
    Card,
    Label,
    Notice,
    Spinner,
    Pagination,
    Select,
    Table,
    Button,
    PeriodSelector,
    Modal,
} from '@givewp/components';
import API, {useLogFetcher, getEndpoint} from './api';

import styles from './styles.module.scss';

const {__} = wp.i18n;

const Logs = () => {
    const [state, setState] = useState({
        initialLoad: false,
        currentPage: 1,
        currentStatus: '', // log type
        currentSource: '',
        currentCategory: '',
        sortColumn: '',
        sortDirection: '',
        startDate: null,
        endDate: null,
        pages: 0,
        statuses: [],
        sources: [],
        categories: [],
        isSorting: false,
    });

    const [logModal, setLogModal] = useState({
        visible: false,
    });

    const [logFlushModal, setLogFlushModal] = useState({
        visible: false,
    });

    const parameters = {
        page: state.currentPage,
        sort: state.sortColumn,
        direction: state.sortDirection,
        type: state.currentStatus,
        source: state.currentSource,
        category: state.currentCategory,
        start: state.startDate ? state.startDate.format('YYYY-MM-DD') : '',
        end: state.endDate ? state.endDate.format('YYYY-MM-DD') : '',
    };

    const {data, isLoading, isError} = useLogFetcher(getEndpoint('/get-logs', parameters), {
        onSuccess: ({response}) => {
            setState((previousState) => {
                return {
                    ...previousState,
                    initialLoad: true,
                    pages: response.pages,
                    statuses: response.statuses,
                    categories: response.categories,
                    sources: response.sources,
                    currentPage: state.currentPage > response.pages ? 1 : state.currentPage,
                    isSorting: false,
                };
            });
        },
    });

    const openLogModal = (log) => {
        setLogModal({
            visible: true,
            id: log.id,
            type: log.log_type,
            category: log.category,
            source: log.source,
            description: log.description,
            date: log.date,
            message: log.message,
            context: log.context,
        });
    };

    const closeLogModal = () => {
        setLogModal({visible: false});
    };

    const openLogFlushModal = (e) => {
        e.preventDefault();
        setLogFlushModal({visible: true});
    };

    const closeLogFlushModal = () => {
        setLogFlushModal({visible: false});
    };

    const flushLogs = () => {
        setLogFlushModal({
            visible: true,
            flushing: true,
        });

        API.delete('/flush-logs')
            .then(() => {
                window.location.reload();
            })
            .catch(() => {
                setLogFlushModal((previousState) => {
                    return {
                        ...previousState,
                        type: 'error',
                        error: true,
                    };
                });
            });
    };

    const setSortDirectionForColumn = (column, direction) => {
        setState((previousState) => {
            return {
                ...previousState,
                sortColumn: column,
                sortDirection: direction,
                isSorting: true,
            };
        });
    };

    const setCurrentPage = (currentPage) => {
        setState((previousState) => {
            return {
                ...previousState,
                currentPage,
            };
        });
    };

    const setCurrentCategory = (e) => {
        const category = e.target.value;
        setState((previousState) => {
            return {
                ...previousState,
                currentCategory: category,
            };
        });
    };

    const setCurrentStatus = (e) => {
        const status = e.target.value;
        setState((previousState) => {
            return {
                ...previousState,
                currentStatus: status,
            };
        });
    };

    const setCurrentSource = (e) => {
        const source = e.target.value;
        setState((previousState) => {
            return {
                ...previousState,
                currentSource: source,
            };
        });
    };

    const setDates = (startDate, endDate) => {
        setState((previousState) => {
            return {
                ...previousState,
                startDate,
                endDate,
            };
        });
    };

    const getCategories = () => {
        const defaultCategory = {
            value: '',
            label: __('All categories', 'give'),
        };

        const categories = Object.values(state.categories).map((label) => {
            return {
                label,
                value: label,
            };
        });

        return [defaultCategory, ...categories];
    };

    const getStatuses = () => {
        const defaultStatus = {
            value: '',
            label: __('All statuses', 'give'),
        };

        const statuses = Object.entries(state.statuses).map(([value, label]) => {
            return {
                label,
                value,
            };
        });

        return [defaultStatus, ...statuses];
    };

    const getSources = () => {
        const defaultSource = {
            value: '',
            label: __('All sources', 'give'),
        };

        const sources = Object.values(state.sources).map((label) => {
            return {
                label,
                value: label,
            };
        });

        return [defaultSource, ...sources];
    };

    const getLogModal = () => {
        return (
            <Modal
                visible={logModal.visible}
                type={logModal.type}
                handleClose={closeLogModal}
                data-givewp-test="log-modal"
            >
                <Modal.Title>
                    <Label type={logModal.type} text={getLogTypeText(logModal.type)} />

                    <strong style={{marginLeft: 20}}>
                        {__('Log ID', 'give')}: {logModal.id}
                    </strong>

                    <Modal.CloseIcon onClick={closeLogModal} data-givewp-test="log-modal-close" />
                </Modal.Title>

                <Modal.Section title={__('Description', 'give')} content={logModal.message} />
                <Modal.Section title={__('Category', 'give')} content={logModal.category} />
                <Modal.Section title={__('Source', 'give')} content={logModal.source} />
                <Modal.Section title={__('Date & Time', 'give')} content={logModal.date} />

                <Modal.AdditionalContext type={logModal.type} context={logModal.context} />
            </Modal>
        );
    };

    const getLogFlushConfirmationModal = () => {
        return (
            <Modal visible={logFlushModal.visible} type={logFlushModal.type} handleClose={closeLogFlushModal}>
                {logFlushModal.flushing ? (
                    <Modal.Content align="center">
                        {logFlushModal.error ? (
                            <>
                                <h2>{__('Something went wrong!', 'give')}</h2>
                                <div>
                                    Try to{' '}
                                    <a onClick={() => window.location.reload()} href="#">
                                        reload
                                    </a>{' '}
                                    the browser
                                </div>
                            </>
                        ) : (
                            <>
                                <Spinner />
                                <div style={{marginTop: 20}}>{__('Flushing logs', 'give')}</div>
                            </>
                        )}
                    </Modal.Content>
                ) : (
                    <>
                        <Modal.Title>{__('Flush all logs', 'give')}</Modal.Title>

                        <Modal.Content>{__('Do you want to flush all logs?', 'give')}</Modal.Content>

                        <Modal.Content>
                            <button
                                style={{marginRight: 20}}
                                className="button button-primary"
                                onClick={flushLogs}
                                data-givewp-test="flush-logs-confirm-btn"
                            >
                                {__('Confirm', 'give')}
                            </button>
                            <button className="button" onClick={closeLogFlushModal}>
                                {__('Cancel', 'give')}
                            </button>
                        </Modal.Content>
                    </>
                )}
            </Modal>
        );
    };

    const getLogTypeText = (type) => {
        if (type in window.GiveLogs.logTypes) {
            return window.GiveLogs.logTypes[type];
        }
        return type;
    };

    const resetQueryParameters = (e) => {
        e.preventDefault();

        // Reset table sort state
        Table.resetSortState();

        setState((previousState) => {
            return {
                ...previousState,
                currentPage: 1,
                currentStatus: '',
                currentSource: '',
                currentCategory: '',
                sortColumn: '',
                sortDirection: '',
                startDate: null,
                endDate: null,
            };
        });
    };

    const columns = [
        {
            key: 'log_type',
            label: __('Status', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('log_type', direction),
        },
        {
            key: 'category',
            label: __('Category', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('category', direction),
        },
        {
            key: 'source',
            label: __('Source', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('source', direction),
        },
        {
            key: 'date',
            label: __('Date/Time', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('date', direction),
        },
        {
            key: 'message',
            label: __('Description', 'give'),
        },
        {
            key: 'details',
            label: __('Details', 'give'),
            append: true,
            styles: {
                maxWidth: 100,
                textAlign: 'center',
                justifyContent: 'center',
            },
        },
    ];

    const columnFilters = {
        log_type: (type) => <Label type={type} text={getLogTypeText(type)} />,
        details: (value, log) => {
            return (
                <Button
                    data-givewp-test="view-log"
                    onClick={(e) => {
                        e.preventDefault();
                        openLogModal(log);
                    }}
                    icon={true}
                >
                    <span className="dashicons dashicons-visibility" />
                </Button>
            );
        },
    };

    // Initial load
    if (!state.initialLoad && isLoading) {
        return (
            <Notice>
                <Spinner />
                <h2>{__('Loading log activity', 'give')}</h2>
            </Notice>
        );
    }

    // Is error?
    if (isError) {
        return (
            <Notice>
                <h2>{__('Something went wrong!', 'give')}</h2>
                <div>
                    Try to{' '}
                    <a onClick={() => window.location.reload()} href="#">
                        reload
                    </a>{' '}
                    the browser
                </div>
            </Notice>
        );
    }

    return (
        <>
            <div className={styles.headerRow}>
                <Select
                    options={getStatuses()}
                    onChange={setCurrentStatus}
                    defaultValue={state.currentStatus}
                    className={styles.headerItem}
                    data-givewp-test="logs-status-dropdown"
                />

                <Select
                    options={getCategories()}
                    onChange={setCurrentCategory}
                    defaultValue={state.currentCategory}
                    className={styles.headerItem}
                    data-givewp-test="logs-category-dropdown"
                />

                <Select
                    options={getSources()}
                    onChange={setCurrentSource}
                    defaultValue={state.currentSource}
                    className={styles.headerItem}
                    data-givewp-test="logs-source-dropdown"
                />

                <PeriodSelector
                    period={{
                        startDate: state.startDate,
                        endDate: state.endDate,
                    }}
                    setDates={setDates}
                />

                <Button onClick={resetQueryParameters}>{__('Reset', 'give')}</Button>

                <div className={styles.pagination}>
                    <Pagination
                        currentPage={state.currentPage}
                        setPage={setCurrentPage}
                        totalPages={state.pages}
                        disabled={isLoading}
                    />
                </div>
            </div>

            <Card>
                <Table
                    columns={columns}
                    data={data}
                    columnFilters={columnFilters}
                    isLoading={isLoading}
                    isSorting={state.isSorting}
                    stripped={false}
                    data-givewp-test="logs-table"
                />
            </Card>

            <div className={styles.footerRow}>
                {data && data.length > 0 && (
                    <button className="button" onClick={openLogFlushModal} data-givewp-test="flush-logs-btn">
                        {__('Flush all logs', 'give')}
                    </button>
                )}

                <div className={styles.pagination}>
                    <Pagination
                        currentPage={state.currentPage}
                        setPage={setCurrentPage}
                        totalPages={state.pages}
                        disabled={isLoading}
                    />
                </div>
            </div>

            {logModal.visible && getLogModal()}
            {logFlushModal.visible && getLogFlushConfirmationModal()}
        </>
    );
};

export default Logs;
