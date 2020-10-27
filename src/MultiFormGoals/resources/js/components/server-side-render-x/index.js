/**
 * NOTE: This is a fork of the original ServerSideRenderX component.
 * You can find more about that project here: https://github.com/dgwyer/server-side-render-x
 *
 * Changes:
 * - some minor clean up was done to remove references to variables which were never actually used.
 */

/**
 * External dependencies
 */
import { isEqual, debounce } from 'lodash';

/**
 * WordPress dependencies
 */
const { Component, RawHTML, Fragment } = wp.element;
const { __, sprintf } = wp.i18n;
const apiFetch = wp.apiFetch;
const { addQueryArgs } = wp.url;
const { Placeholder } = wp.components;

export function rendererPath( block, attributes = null, urlQueryArgs = {} ) {
	return addQueryArgs( `/wp/v2/block-renderer/${ block }`, {
		context: 'edit',
		...( null !== attributes ? { attributes } : {} ),
		...urlQueryArgs,
	} );
}

export class ServerSideRenderX extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			response: null,
			prevResponse: null,
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch( this.props );
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		this.fetch = debounce( this.fetch, 500 );
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps ) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
	}

	fetch( props ) {
		if ( ! this.isStillMounted ) {
			return;
		}
		if ( null !== this.state.response ) {
			this.setState( state => (
				{
					response: null,
					prevResponse: state.response,
				}
			) );
		}
		const { block, attributes = null, urlQueryArgs = {} } = props;

		const path = rendererPath( block, attributes, urlQueryArgs );
		// Store the latest fetch request so that when we process it, we can
		// check if it is the current request, to avoid race conditions on slow networks.
		const fetchRequest = ( this.currentFetchRequest = apiFetch( { path } )
			.then( ( response ) => {
				if (
					this.isStillMounted &&
					fetchRequest === this.currentFetchRequest &&
					response
				) {
					this.setState( { response: response.rendered } );
				}
			} )
			.catch( ( error ) => {
				if (
					this.isStillMounted &&
					fetchRequest === this.currentFetchRequest
				) {
					this.setState( {
						response: {
							error: true,
							errorMsg: error.message,
						},
					} );
				}
			} ) );
		return fetchRequest;
	}

	render() {
		const { right, top, unit } = this.props.spinnerLocation;
		const response = this.state.response;
		const prevResponse = this.state.prevResponse;
		let prevResponseHTML = '';
		if ( prevResponse !== null ) {
			prevResponseHTML = `<div style="position:relative;"><div style="position:absolute;right:${ right }${ unit };top:${ top }${ unit };z-index:1"><span class="components-spinner"></span></div>${ prevResponse }</div>`;
		}

		const {
			className,
			EmptyResponsePlaceholder,
			ErrorResponsePlaceholder,
		} = this.props;

		if ( response === '' ) {
			return (
				<EmptyResponsePlaceholder
					response={ response }
					{ ...this.props }
				/>
			);
		} else if ( ! response ) {
			return (
				<Fragment>
					<RawHTML key="html" className={ className }>
						{ prevResponseHTML }
					</RawHTML>
				</Fragment>
			);
		} else if ( response.error ) {
			return (
				<ErrorResponsePlaceholder
					response={ response }
					{ ...this.props }
				/>
			);
		}

		return (
			<RawHTML key="html" className={ className }>
				{ response }
			</RawHTML>
		);
	}
}

ServerSideRenderX.defaultProps = {
	EmptyResponsePlaceholder: ( { className } ) => (
		<Placeholder className={ className }>
			{ __( 'Block rendered as empty.' ) }
		</Placeholder>
	),
	ErrorResponsePlaceholder: ( { response, className } ) => {
		const errorMessage = sprintf(
			// translators: %s: error message describing the problem
			__( 'Error loading block: %s' ),
			response.errorMsg
		);
		return (
			<Placeholder className={ className }>{ errorMessage }</Placeholder>
		);
	},
};

export default ServerSideRenderX;
