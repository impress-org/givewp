/**
 * Block dependencies
 */
import GiveBlankSlate from '../../components/blank-slate/index';
import NoForms from './form/none';
import FormPreview from './form/preview';
import FormSelect from './form/select';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const { withAPIData } = wp.components;
const { Component } = wp.element;

/**
 * Render Block UI For Editor
 *
 * @class GiveForm
 * @extends {Component}
 */
class GiveForm extends Component {
	constructor() {
		super( ...arguments );
		this.doServerSideRender = this.doServerSideRender.bind( this );
		this.updateButtonTitle = this.updateButtonTitle.bind( this );
		this.state = {
			html: '',
			error: false,
			fetching: false,
			isButtonTitleUpdated: false,
		};
	}

	/************************
	 * Component Lifecycle
	 ************************/

	/**
	 * If form id found render preview
	 *
	 * @memberof GiveForm
	 */
	componentDidMount() {
		if ( this.props.attributes.id ) {
			this.setState( { fetching: true } );
			this.doServerSideRender();
		}
	}

	/**
	 * can't abort the fetch promise, so let it know we will unmount
	 *
	 * @memberof GiveForm
	 */
	componentWillUnmount() {
		this.unmounting = true;
	}

	/**
	 * Re-render preview if attribute(s) have changed
	 *
	 * @param {any} prevProps component previous props
	 * @memberof GiveForm
	 */
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

	/*********************
	 * Component Render
	**********************/

	/**
	 * Render and get form preview from server
	 *
	 * @memberof GiveForm
	 */
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
		window.fetch( `${ wpApiSettings.root }give-api/v1/form/${ attributes.id }/?${ parameters.join( '&' ) }` ).then(
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

	/**
	 * Update state whether form button text is updated or not
	 *
	 * @param {any} status true/false
	 * @memberof GiveForm
	 */
	updateButtonTitle( status ) {
		this.setState( { isButtonTitleUpdated: status } );
	}

	/**
	 * Render block UI
	 *
	 * @returns {object} JSX Object
	 * @memberof GiveForm
	 */
	render() {
		const props = this.props;
		const attributes = props.attributes;
		const { html, fetching, isButtonTitleUpdated } = this.state;

		// Render block UI
		let blockUI;

		if ( ( ! attributes.id && ! props.forms.data ) || fetching ) {
			blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader />;
		} else if ( ! attributes.id && props.forms.data.length === 0 ) {
			blockUI = <NoForms />;
		} else if ( ! attributes.id ) {
			blockUI =	<FormSelect { ... { ...props } } />;
		} else {
			blockUI = <FormPreview
				html={ html }
				isButtonTitleUpdated={ isButtonTitleUpdated }
				updateButtonTitle={ this.updateButtonTitle }
				doServerSideRender={ this.doServerSideRender }
				{ ... { ...props } } />;
		}

		return ( <div className={ props.className } key="GiveBlockUI">{ blockUI }</div> );
	}
}

/**
 * Export component attaching withAPIdata
*/
export default withAPIData( ( ) => {
	return {
		forms: '/wp/v2/give_forms',
	};
} )( GiveForm );
