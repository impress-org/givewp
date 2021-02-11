import { useState } from 'react';
import classNames from 'classnames';
import { Card, Label, Notice, Spinner, Pagination, Table, Button, Modal } from 'GiveComponents';
import API, { useMigrationFetcher, getEndpoint } from './api';

import styles from './styles.module.scss';

const { __ } = wp.i18n;

const Migrations = () => {
	const [ state, setState ] = useState( {
		initialLoad: false,
		currentPage: 1,
		sortColumn: '',
		sortDirection: '',
		pages: 0,
	} );

	const [ migrationModal, setMigrationModal ] = useState( {
		visible: false,
	} );

	const [ migrationRunModal, setMigrationRunModal ] = useState( {
		visible: false,
	} );

	const parameters = {
		page: state.currentPage,
		sort: state.sortColumn,
		direction: state.sortDirection,
	};

	const { data, isLoading, isError } = useMigrationFetcher( getEndpoint( '/get-migrations', parameters ), {
		onSuccess: ( { response } ) => {
			setState( ( previousState ) => {
				return {
					...previousState,
					initialLoad: true,
					pages: response.pages,
					statuses: response.statuses,
					categories: response.categories,
					sources: response.sources,
					currentPage: state.currentPage > response.pages ? 1 : state.currentPage,
				};
			} );
		},
	} );

	const runMigration = () => {
		setMigrationRunModal( ( previousState ) => {
			return {
				...previousState,
				visible: true,
				running: true,
			};
		} );

		API.post( '/run-migration', { id: migrationRunModal.id } )
			.then( () => {
				window.location.reload();
			} )
			.catch( () => {
				setMigrationRunModal( ( previousState ) => {
					return {
						...previousState,
						type: 'error',
						error: true,
					};
				} );
			} );
	};

	const openMigrationModal = ( migration ) => {
		setMigrationModal( {
			visible: true,
			id: migration.id,
			status: migration.status,
			error: migration.error,
			last_run: migration.last_run,
		} );
	};

	const closeMigrationModal = () => {
		setMigrationModal( { visible: false } );
	};

	const openMigrationRunModal = ( e ) => {
		e.preventDefault();
		setMigrationRunModal( {
			visible: true,
			type: 'warning',
		} );
	};

	const closeMigrationRunModal = () => {
		setMigrationRunModal( { visible: false } );
	};

	const setSortDirectionForColumn = ( column, direction ) => {
		setState( ( previousState ) => {
			return {
				...previousState,
				sortColumn: column,
				sortDirection: direction,
			};
		} );
	};

	const setCurrentPage = ( currentPage ) => {
		setState( ( previousState ) => {
			return {
				...previousState,
				currentPage,
			};
		} );
	};

	const getMigrationModal = () => {
		return (
			<Modal visible={ migrationModal.visible } type="error" handleClose={ closeMigrationModal }>
				<Modal.Title>
					<strong>
						{ __( 'Migration Failed', 'give' ) }
					</strong>

					<Modal.CloseIcon onClick={ closeMigrationModal } />
				</Modal.Title>

				<Modal.Section title={ __( 'Migration ID', 'give' ) } content={ migrationModal.id } />
				<Modal.Section title={ __( 'Last run', 'give' ) } content={ migrationModal.last_run ?? 'n/a' } />

				<Modal.AdditionalContext type={ migrationModal.status } context={ migrationModal.error } />
			</Modal>
		);
	};

	const getMigrationRunModal = () => {
		return (
			<Modal visible={ migrationRunModal.visible } type={ migrationRunModal.type } handleClose={ closeMigrationRunModal }>
				{ migrationRunModal.running ? (
					<Modal.Content align="center">
						{ migrationRunModal.error ? (
							<>
								<h2>{ __( 'Something went wrong!', 'give' ) }</h2>
								<div>
									Try to <a onClick={ () => window.location.reload() } href="#">reload</a> the browser
								</div>
							</>
						) : (
							<>
								<Spinner />
								<div style={ { marginTop: 20 } }>
									{ __( 'Running Update', 'give' ) }
								</div>
							</>
						) }
					</Modal.Content>
				) : (
					<>
						<Modal.Title>
							<span className={ classNames( styles.titleIcon, styles.warning ) }>
								<span className="dashicons dashicons-warning"></span>
							</span>
							{ __( 'Create a Backup Before Running Database Update', 'give' ) }
						</Modal.Title>

						<Modal.Content>
							<strong style={ { marginRight: 5 } }>{ __( 'Notice', 'give' ) }:</strong>
							{ __( 'We strongly recommend you create a complete backup of your WordPress files and database prior to performing an update. We are not responsible for any misuse, deletions, white screens, fatal errors, or any other issue resulting from the use of this plugin.', 'give' ) }
						</Modal.Content>

						<Modal.Content>
							<button style={ { marginRight: 20 } } className="button button-primary" onClick={ runMigration }>
								{ __( 'Confirm', 'give' ) }
							</button>
							<button className="button" onClick={ closeMigrationRunModal }>
								{ __( 'Cancel', 'give' ) }
							</button>
						</Modal.Content>
					</>
				) }
			</Modal>
		);
	};

	const columns = [
		{
			key: 'status',
			label: __( 'Status', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'status', direction ),
			styles: {
				maxWidth: 150,
			},
		},
		{
			key: 'id',
			label: __( 'Migration ID', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'id', direction ),
		},
		{
			key: 'last_run',
			label: __( 'Last run', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'last_run', direction ),
		},
		{
			key: 'actions',
			label: __( 'Actions', 'give' ),
			append: true,
		},
		{
			key: 'details',
			label: __( 'Details', 'give' ),
			append: true,
			styles: {
				maxWidth: 100,
				textAlign: 'center',
				justifyContent: 'center',
			},
		},
	];

	const columnFilters = {
		status: ( type ) => <Label type={ type } />,
		actions: ( type, migration ) => {
			if ( 'success' !== migration.status ) {
				return (
					<button
						className="button"
						onClick={ openMigrationRunModal }>
						{ __( 'Run Update', 'give' ) }
					</button>
				);
			}
		},
		details: ( value, migration ) => {
			if ( migration.status === 'success' ) {
				return null;
			}

			return (
				<Button
					onClick={ ( e ) => {
						e.preventDefault();
						openMigrationModal( migration );
					} }
					icon={ true }>
					<span className="dashicons dashicons-visibility" />
				</Button>
			);
		},
	};

	// Initial load
	if ( ! state.initialLoad && isLoading ) {
		return (
			<Notice>
				<Spinner />
				<h2>{ __( 'Loading migrations activity', 'give' ) }</h2>
			</Notice>
		);
	}

	// Is error?
	if ( isError ) {
		return (
			<Notice>
				<h2>{ __( 'Something went wrong!', 'give' ) }</h2>
				<div>
					Try to <a onClick={ () => window.location.reload() } href="#">reload</a> the browser
				</div>
			</Notice>
		);
	}

	return (
		<>
			<Card>
				<Table
					columns={ columns }
					data={ data }
					columnFilters={ columnFilters }
					isLoading={ isLoading }
					stripped={ false }
				/>
			</Card>

			<div className={ styles.footerRow }>
				<div className={ styles.pagination }>
					<Pagination
						currentPage={ state.currentPage }
						setPage={ setCurrentPage }
						totalPages={ state.pages }
						disabled={ isLoading }
					/>
				</div>
			</div>

			{ migrationModal.visible && getMigrationModal() }
			{ migrationRunModal.visible && getMigrationRunModal() }
		</>
	);
};

export default Migrations;
