/**
 * Block dependencies
 */
import Inspector from '../edit/inspector';
import Controls from '../edit/controls';
import GiveBlankSlate from '../../components/blank-slate/index';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const {
	Button,
	SelectControl,
} = wp.components;
const { Component } = wp.element;

class GiveForm extends Component {
	constructor() {
		super( ...arguments );
		this.doServerSideRender = this.doServerSideRender.bind( this );
		this.state = {
			html: '',
			error: false,
			fetching: false,
			isButtonTitleUpdated: false,
		};
	}

	doServerSideRender() {
		const attributes = this.props.attributes;
		const parameters = [
			`show_title=${ attributes.showTitle.toString() }`,
			`show_goal=${ attributes.showGoal.toString() }`,
			`show_content=${ attributes.showContent.toString() }`,
			`display_style=${ attributes.displayStyle }`,
		];
		if ( 'reveal' === attributes.displayStyle ) {
			parameters.push( `continue_button_title=${ attributes.continueButtonTitle }` );
		}
		this.setState( { error: false, fetching: true } );
		window.fetch( `${ wpApiSettings.schema.url }/wp-json/give-api/v1/form/${ attributes.id }/?${ parameters.join( '&' ) }` ).then(
			( response ) => {
				response.json().then( ( obj ) => {
					if ( this.unmounting ) {
						return;
					}

					const { html } = obj;

					if ( html ) {
						this.setState( { html } );
					} else {
						this.setState( { error: true } );
					}
					this.setState( { fetching: false } );
				} );
			}
		);
	}

	getFormsFromServer() {
		this.setState( { error: false } );

		// Fetch from API if key exist
		if ( window.give_blocks_vars.key !== null ) {
			window.fetch( `${ wpApiSettings.schema.url }/give-api/forms/?key=${ window.give_blocks_vars.key }&token=${ window.give_blocks_vars.token }` ).then(
				( response ) => {
					response.json().then( ( obj ) => {
						if ( this.unmounting ) {
							return;
						}

						const { forms } = obj;

						if ( forms ) {
							this.props.setAttributes( { forms: forms } );
						} else {
							this.setState( { error: true } );
						}
					} );
				}
			);
		} else {
			this.setState( { fetching: false } );
		}
	}

	componentDidMount() {
		if ( this.props.attributes.id ) {
			this.setState( { fetching: true } );
			this.doServerSideRender();
		} else {
			this.getFormsFromServer();
		}
	}

	componentWillUnmount() {
		// can't abort the fetch promise, so let it know we will unmount
		this.unmounting = true;
	}

	componentDidUpdate( prevProps ) {
		const currentAttributes = this.props.attributes;
		const prevAttributes = prevProps.attributes;

		if (
			currentAttributes.id !== prevAttributes.id ||
			currentAttributes.showTitle !== prevAttributes.showTitle ||
			currentAttributes.showGoal !== prevAttributes.showGoal ||
			currentAttributes.displayStyle !== prevAttributes.displayStyle ||
			currentAttributes.contentDisplay !== prevAttributes.contentDisplay ||
			currentAttributes.showContent !== prevAttributes.showContent
		) {
			this.setState( { fetching: true } );
			this.doServerSideRender();
		}
	}

	render() {
		const props = this.props;
		const attributes = props.attributes;
		const { html, fetching, isButtonTitleUpdated } = this.state;

		const getFormOptions = () => {
			const formOptions = attributes.forms.map( ( form ) => {
				return {
					value: form.info.id,
					label: form.info.title === '' ? `${ form.info.id }: No form title` : form.info.title,
				};
			} );

			// Default option
			formOptions.unshift( { value: '-1', label: 'Select a Donation Form...' } );

			return formOptions;
		};

		const onChangeForm = () => {
			props.setAttributes( { id: 0 } );
			this.getFormsFromServer();
		};

		const setFormIdTo = id => {
			props.setAttributes( { id: id } );
		};

		const setDisplayStyleTo = format => {
			props.setAttributes( { displayStyle: format } );
		};

		const setContinueButtonTitle = buttonTitle => {
			props.setAttributes( { continueButtonTitle: buttonTitle } );
			if ( ! isButtonTitleUpdated ) {
				this.setState( { isButtonTitleUpdated: true } );
			}
		};

		const toggleShowTitle = () => {
			props.setAttributes( { showTitle: ! attributes.showTitle } );
		};

		const toggleShowGoal = () => {
			props.setAttributes( { showGoal: ! attributes.showGoal } );
		};

		const toggleContentDisplay = () => {
			props.setAttributes( { contentDisplay: ! attributes.contentDisplay } );

			// Set form Content Display Position
			if ( ! attributes.contentDisplay ) {
				props.setAttributes( { showContent: 'above' } ); // true && above
			} else if ( !! attributes.contentDisplay ) {
				props.setAttributes( { showContent: 'none' } ); // false && none
			}
		};

		const setShowContentPosition = position => {
			props.setAttributes( { showContent: position } );
		};

		const updateContinueButtonTitle = () => {
			if ( isButtonTitleUpdated ) {
				this.doServerSideRender();
				this.setState( { isButtonTitleUpdated: false } );
			}
		};

		if ( give_blocks_vars.key === null ) {
			/* No API Key generated*/
			return (
				<div className={ props.className }>
					<GiveBlankSlate title={ __( 'No API key found.' ) }
						description={ __( 'The first step towards using new blocks based experience is to generate API key .' ) }
						helpLink>
						<Button isPrimary isLarge href={ `${ wpApiSettings.schema.url }/wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=api` }> { __( 'Generate API Key' ) } </Button>
					</GiveBlankSlate>
				</div>
			);
		} else if ( ( ! attributes.id && ! attributes.forms ) || fetching ) {
			/* Fetching Data */
			return (
				<div className={ props.className }>
					<GiveBlankSlate title={ __( 'Loading...' ) } isLoader />
				</div>
			);
		} else if ( ! attributes.id && attributes.forms.length === 0 ) {
			/* No form created */
			return (
				<div className={ props.className }>
					<GiveBlankSlate title={ __( 'No donation forms found.' ) }
						description={ __( 'The first step towards accepting online donations is to create a form.' ) }
						helpLink>
						<Button isPrimary
							isLarge
							href={ `${ wpApiSettings.schema.url }/wp-admin/post-new.php?post_type=give_forms` }>
							{ __( 'Create Donation Form' ) }
						</Button>
					</GiveBlankSlate>
				</div>
			);
		} else if ( ! attributes.id ) {
			/* No for selected */
			return (
				<div className={ props.className }>
					<GiveBlankSlate title={ __( 'Give Donation form' ) }>
						<SelectControl
							options={ getFormOptions() }
							onChange={ setFormIdTo }
						/>

						<Button isPrimary
							isLarge href={ `${ wpApiSettings.schema.url }/wp-admin/post-new.php?post_type=give_forms` }>
							{ __( 'Add new form' ) }
						</Button>
					</GiveBlankSlate>
				</div>
			);
		}

		return (
			<div id="donation-form-preview-block" className={ props.className }>
				{ !! props.isSelected &&
					<Inspector { ... {
						setDisplayStyleTo,
						setContinueButtonTitle,
						updateContinueButtonTitle,
						toggleShowTitle,
						toggleShowGoal,
						toggleContentDisplay,
						setShowContentPosition,
						...props }
						} />
				}

				{ !! props.isSelected &&
					<Controls { ... {
						onChangeForm,
						...props,
					} } />

				}
				<div dangerouslySetInnerHTML={ { __html: html } }>
				</div>
			</div>
		);
	}
}

export default GiveForm;
