import {useState} from 'react';
import classNames from 'classnames';
import {Card, Label, Notice, Spinner, Pagination, Table, Button, Modal} from '@givewp/components';
import API, {useMigrationFetcher, getEndpoint} from './api';

import styles from './styles.module.scss';

const {__} = wp.i18n;

const Migrations = () => {
    const [state, setState] = useState({
        initialLoad: false,
        currentPage: 1,
        sortColumn: 'run_order',
        sortDirection: 'asc',
        pages: 0,
        showOptions: false,
        isSorting: false,
    });

    const [migrationModal, setMigrationModal] = useState({
        visible: false,
    });

    const [migrationRunModal, setMigrationRunModal] = useState({
        visible: false,
    });

    const parameters = {
        page: state.currentPage,
        sort: state.sortColumn,
        direction: state.sortDirection,
    };

    const {data, isLoading, isError, mutate} = useMigrationFetcher(getEndpoint('/get-migrations', parameters), {
        onSuccess: ({response}) => {
            setState((previousState) => {
                return {
                    ...previousState,
                    initialLoad: true,
                    pages: response.pages,
                    currentPage: state.currentPage > response.pages ? 1 : state.currentPage,
                    showOptions: response.showOptions,
                    isSorting: false,
                };
            });
        },
    });

    const runMigration = () => {
        setMigrationRunModal((previousState) => {
            return {
                ...previousState,
                visible: true,
                running: true,
            };
        });

        API.post('/run-migration', {id: migrationRunModal.id})
            .then((response) => {
                if (response.data.status) {
                    closeMigrationRunModal();
                } else {
                    setMigrationRunModal((previousState) => {
                        return {
                            ...previousState,
                            type: 'error',
                            error: true,
                            errorMessage: response.data.message,
                        };
                    });
                }
                // Invalidate the cache
                mutate(getEndpoint('/get-migrations', parameters));
            })
            .catch(() => {
                setMigrationRunModal((previousState) => {
                    return {
                        ...previousState,
                        type: 'error',
                        error: true,
                    };
                });
            });
    };

    const openMigrationModal = (migration) => {
        setMigrationModal({
            visible: true,
            id: migration.id,
            status: migration.status,
            error: migration.error,
            last_run: migration.last_run,
        });
    };

    const closeMigrationModal = () => {
        setMigrationModal({visible: false});
    };

    const openMigrationRunModal = (migrationId) => {
        setMigrationRunModal({
            id: migrationId,
            visible: true,
            type: 'warning',
        });
    };

    const closeMigrationRunModal = () => {
        setMigrationRunModal({visible: false});
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

    const getMigrationModal = () => {
        return (
            <Modal visible={migrationModal.visible} type="error" handleClose={closeMigrationModal}>
                <Modal.Title>
                    <strong>{__('Migration Failed', 'give')}</strong>

                    <Modal.CloseIcon onClick={closeMigrationModal} />
                </Modal.Title>

                <Modal.Section title={__('Migration ID', 'give')} content={migrationModal.id} />
                <Modal.Section title={__('Last run', 'give')} content={migrationModal.last_run ?? 'n/a'} />

                <Modal.AdditionalContext type={migrationModal.status} context={migrationModal.error} />
            </Modal>
        );
    };

    const getMigrationRunModal = () => {
        return (
            <Modal
                visible={migrationRunModal.visible}
                type={migrationRunModal.type}
                handleClose={closeMigrationRunModal}
            >
                {migrationRunModal.running ? (
                    <Modal.Content align="center">
                        {migrationRunModal.error ? (
                            <>
                                <Modal.CloseIcon onClick={closeMigrationRunModal} />
                                <h2>{__('Database update failed!', 'give')}</h2>
                                {migrationRunModal.errorMessage && (
                                    <Modal.Content align="center">{migrationRunModal.errorMessage}</Modal.Content>
                                )}
                                <Modal.Content align="center">
                                    {__('Check migration details for more information', 'give')}
                                </Modal.Content>
                            </>
                        ) : (
                            <>
                                <Spinner />
                                <div style={{marginTop: 20}}>{__('Running Update', 'give')}</div>
                            </>
                        )}
                    </Modal.Content>
                ) : (
                    <>
                        <Modal.Title>
                            <span className={classNames(styles.titleIcon, styles.warning)}>
                                <span className="dashicons dashicons-warning" />
                            </span>
                            {__('Create a Backup Before Running Database Update', 'give')}
                        </Modal.Title>

                        <Modal.Content>
                            <strong style={{marginRight: 5}}>{__('Notice', 'give')}:</strong>
                            {__(
                                'We strongly recommend you create a complete backup of your WordPress files and database prior to performing an update. We are not responsible for any misuse, deletions, white screens, fatal errors, or any other issue resulting from the use of this plugin.',
                                'give'
                            )}
                        </Modal.Content>

                        <Modal.Content>
                            <button style={{marginRight: 20}} className="button button-primary" onClick={runMigration}>
                                {__('Confirm', 'give')}
                            </button>
                            <button className="button" onClick={closeMigrationRunModal}>
                                {__('Cancel', 'give')}
                            </button>
                        </Modal.Content>
                    </>
                )}
            </Modal>
        );
    };

    const columns = [
        {
            key: 'status',
            label: __('Status', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('status', direction),
            styles: {
                maxWidth: 150,
            },
        },
        {
            key: 'title',
            label: __('Migration Title', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('title', direction),
            styles: {
                overflowWrap: 'break-word',
                wordWrap: 'break-word',
                wordBreak: 'break-all',
            },
        },
        {
            key: 'last_run',
            label: __('Last run', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('last_run', direction),
            styles: {
                maxWidth: 200,
            },
        },
        {
            key: 'source',
            label: __('Source', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('source', direction),
            styles: {
                maxWidth: 200,
            },
        },
        {
            key: 'run_order',
            label: __('Run Order', 'give'),
            sort: true,
            sortCallback: (direction) => setSortDirectionForColumn('run_order', direction),
            styles: {
                maxWidth: 150,
            },
        },
        {
            key: 'actions',
            label: __('Actions', 'give'),
            append: true,
            styles: {
                maxWidth: 100,
            },
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
        status: (type) => <Label type={type} />,
        actions: (type, migration) => {
            if (!state.showOptions && migration.status !== 'failed') {
                return null;
            }

            return (
                <button className="button" onClick={() => openMigrationRunModal(migration.id)}>
                    {__('Re-run Update', 'give')}
                </button>
            );
        },
        details: (value, migration) => {
            if (!migration.error.length) {
                return null;
            }

            return (
                <Button
                    onClick={(e) => {
                        e.preventDefault();
                        openMigrationModal(migration);
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
                <h2>{__('Loading updates activity', 'give')}</h2>
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
            <Card>
                <Table
                    columns={columns}
                    data={data}
                    columnFilters={columnFilters}
                    isLoading={isLoading}
                    isSorting={state.isSorting}
                    stripped={false}
                />
            </Card>

            <div className={styles.footerRow}>
                <div className={styles.pagination}>
                    <Pagination
                        currentPage={state.currentPage}
                        setPage={setCurrentPage}
                        totalPages={state.pages}
                        disabled={isLoading}
                    />
                </div>
            </div>

            {migrationModal.visible && getMigrationModal()}
            {migrationRunModal.visible && getMigrationRunModal()}
        </>
    );
};

export default Migrations;
