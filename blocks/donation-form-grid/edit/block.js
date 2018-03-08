/**
 * Block dependencies
 */
import GiveBlankSlate from '../../components/blank-slate/index';
import FormGridPreview from './components/preview';

/**
 * Internal dependencies
 */
const {__}          = wp.i18n;
const {withAPIData} = wp.components;
const {Component}   = wp.element;

/**
 * Render Block UI For Editor
 *
 * @class GiveDonationFormGrid
 * @extends {Component}
 */
class GiveDonationFormGrid extends Component {
	constructor(props) {
		super(...props);
		this.doServerSideRender = this.doServerSideRender.bind(this);
		this.state              = {
			html: '',
			error: false,
			fetching: false,
		};
	}

	/************************
	 * Component Lifecycle
	 ************************/

	/**
	 * If form id found render preview
	 *
	 * @memberof GiveDonationFormGrid
	 */
	componentDidMount() {
		this.doServerSideRender();
	}

	/**
	 * can't abort the fetch promise, so let it know we will unmount
	 *
	 * @memberof GiveDonationFormGrid
	 */
	componentWillUnmount() {
		this.unmounting = true;
	}

	/**
	 * Re-render preview if attribute(s) have changed
	 *
	 * @param {any} prevProps component previous props
	 * @memberof GiveDonationFormGrid
	 */
	componentDidUpdate(prevProps) {
		const currentAttributes = this.props.attributes;
		const prevAttributes    = prevProps.attributes;

		if (
			currentAttributes.columns !== prevAttributes.columns ||
			currentAttributes.showExcerpt !== prevAttributes.showExcerpt ||
			currentAttributes.showGoal !== prevAttributes.showGoal ||
			currentAttributes.showFeaturedImage !== prevAttributes.showFeaturedImage ||
			currentAttributes.displayType !== prevAttributes.displayType
		) {
			this.setState({fetching: true});
			this.doServerSideRender();
		}
	}

	/*********************
	 * Component Render
	 **********************/

	/**
	 * Render and get form preview from server
	 *
	 * @memberof GiveDonationFormGrid
	 */
	doServerSideRender() {
		const attributes = this.props.attributes;
		const parameters = [
			`columns=${ attributes.columns.toString() }`,
			`show_goal=${ attributes.showGoal.toString() }`,
			`show_excerpt=${ attributes.showExcerpt.toString() }`,
			`show_featured_image=${ attributes.showFeaturedImage }`,
			`display_type=${ attributes.displayType }`,
		];

		this.setState({error: false, fetching: true});
		window.fetch(`${ giveApiSettings.root }form-grid/?${ parameters.join('&') }`).then(
			(response) => {
				response.json().then((obj) => {
					if (this.unmounting) {
						return;
					}

					const {html} = obj;

					if (html) {
						this.setState({html});
					} else {
						this.setState({error: true});
					}

					this.setState({fetching: false});
				});
			}
		);
	}

	/**
	 * Render block UI
	 *
	 * @returns {object} JSX Object
	 * @memberof GiveDonationFormGrid
	 */
	render() {
		const props            = this.props;
		const attributes       = props.attributes;
		const {html, fetching} = this.state;

		// Render block UI
		let blockUI;

		if (fetching) {
			blockUI = <GiveBlankSlate title={__('Loading...')} isLoader/>;
		} else if (!html.length) {
			blockUI = 'no form';
		} else {
			blockUI = <FormGridPreview
				html={html}
				doServerSideRender={this.doServerSideRender}
				{... {...props}} />;
		}

		return (<div className={props.className} key="GiveDonationFormGridBlockUI">{blockUI}</div>);
	}
}

/**
 * Export component attaching withAPIdata
 */
export default withAPIData(() => {
	return {
		forms: '/give-api/v2/form-grid',
	};
})(GiveDonationFormGrid);
