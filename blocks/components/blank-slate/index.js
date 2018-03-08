/**
* Block dependencies
*/
import GiveHelpLink from '../help-link/index';
import PlaceholderContainerAnimation from '../container-placeholder-animation/index';
import './style.scss';

/**
* Internal dependencies
*/
const { __ } = wp.i18n;

const GiveBlankSlate = ( props ) => {
	const {
		noIcon,
		isLoader,
		title,
		description,
		children,
		helpLink,
	} = props;


	// @todo: do not hard code wp content url that can be configure.
	const giveLogo = '/wp-content/plugins/Give/assets/dist/images/give-icon-full-circle.svg';

	const block_loading = (
		<PlaceholderContainerAnimation />
	);

	const block_loaded = (
		<div className="block-loaded">
			{ !! title && ( <h2 className="give-blank-slate__heading">{ title }</h2> ) }
			{ !! description && ( <p className="give-blank-slate__message">{ description }</p> ) }
			{ children }
			{ !! helpLink && ( <GiveHelpLink /> ) }
		</div>
	);

	return (
		<div className="give-blank-slate">
			{ ! noIcon && (
			<img className="give-blank-slate__image"
				src={ `${ wpApiSettings.schema.url }${ giveLogo }` }
				alt={ __( 'Give Icon' ) } />
			) }
			{ !! isLoader ? block_loading : block_loaded }
		</div>
	);
};

export default GiveBlankSlate;
