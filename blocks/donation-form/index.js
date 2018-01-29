/**
 * Block dependencies
 */
import './style.scss';

const { __ } = wp.i18n;
const {
	registerBlockType,
	InspectorControls,
	BlockDescription,
} = wp.blocks;
const {
	ToggleControl,
	SelectControl,
	TextControl,
} = InspectorControls;
const {
	PanelBody,
	Spinner,
} = wp.components;
const { Component } = wp.element;

export default registerBlockType( 'give/donation-form', {

	title: __( 'Give Donation Form' ),
	category: 'common',
	supportHTML: false,

	attributes: {
		id: {
			type: 'number',
			default: 0,
		},
		displayStyle: {
			type: 'string',
			default: 'onpage',
		},
		continueButtonTitle: {
			type: 'string',
			default: '',
		},
		showTitle: {
			type: 'boolean',
			default: false,
		},
		showGoal: {
			type: 'boolean',
			default: false,
		},
		contentDisplay: {
			type: 'boolean',
			default: false,
		},
		showContent: {
			type: 'string',
			default: 'none',
		},
	},

	edit: class extends Component {
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

			const displayStyles = [
				{ value: 'onpage', label: 'Full Form' },
				{ value: 'modal', label: 'Modal' },
				{ value: 'reveal', label: 'Reveal' },
				{ value: 'button', label: 'One-button Launch' },
			];

			const contentPosition = [
				{ value: 'above', label: 'Above' },
				{ value: 'below', label: 'Below' },
			];

			const getFormOptions = () => {
				const formOptions = attributes.forms.map( ( form ) => {
					return {
						value: form.info.id,
						label: form.info.title,
					};
				} );

				// Default option
				formOptions.unshift( { value: '-1', label: 'Select a Donation Form...' } );

				return formOptions;
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

			const inspectorControls = (
				<InspectorControls key="inspector">
					<BlockDescription>
						<p>{ __( 'The Give Donation Form block insert an existing donation form into the page. Each form\'s presentation can be customized below.' ) }</p>
					</BlockDescription>
					<PanelBody title={ __( 'Presentation' ) }>
						<SelectControl
							label={ __( 'Format' ) }
							value={ attributes.displayStyle }
							options={ displayStyles }
							onChange={ setDisplayStyleTo }
						/>
						{
							'reveal' === attributes.displayStyle && (
								<TextControl
									label={ __( 'Continue Button Title' ) }
									value={ attributes.continueButtonTitle }
									onChange={ setContinueButtonTitle }
									onBlur={ updateContinueButtonTitle }
								/>
							)
						}
					</PanelBody>
					<PanelBody title={ __( 'Form Components' ) }>
						<ToggleControl
							label={ __( 'Form Title' ) }
							checked={ !! attributes.showTitle }
							onChange={ toggleShowTitle }
						/>
						<ToggleControl
							label={ __( 'Form Goal' ) }
							checked={ !! attributes.showGoal }
							onChange={ toggleShowGoal }
						/>
						<ToggleControl
							label={ __( 'Form Content' ) }
							checked={ !! attributes.contentDisplay }
							onChange={ toggleContentDisplay }
						/>
						{
							attributes.contentDisplay && (
								<SelectControl
									label={ __( 'Content Position' ) }
									value={ attributes.showContent }
									options={ contentPosition }
									onChange={ setShowContentPosition }
								/>
							)
						}
					</PanelBody>
				</InspectorControls>
			);

			if ( ! attributes.id && ! attributes.forms ) {
				return [
					<div key="loading" className="wp-block-embed is-loading">
						<Spinner />
						<p>{ __( 'Loading.…' ) }</p>
					</div>,
				];
			}

			if ( ! attributes.id && attributes.forms.length === 0 ) {
				return 'No forms';
			}

			if ( ! attributes.id ) {
				return (
					<div>
						<SelectControl
							label={ __( 'Give Donation Form' ) }
							options={ getFormOptions() }
							onChange={ setFormIdTo }
						/>
					</div>
				);
			}

			if ( fetching ) {
				return [
					<div key="loading" className="wp-block-embed is-loading">
						<Spinner />
						<p>{ __( 'Loading…' ) }</p>
					</div>,
				];
			}

			return (
				<div id="donation-form-preview-block">
					{ !! props.focus && inspectorControls }
					<div dangerouslySetInnerHTML={ { __html: html } }>
					</div>
				</div>
			);
		}
	},

	save: () => {
		return null;
	},
} );
