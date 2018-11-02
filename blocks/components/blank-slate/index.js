/**
* WordPress dependencies
*/
const { __ } = wp.i18n;

/**
* Internal dependencies
*/
import { getSiteUrl } from '../../utils';
import GiveHelpLink from '../help-link';
import PlaceholderAnimation from '../placeholder-animation';
import './style.scss';

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

	const blockLoading = (
		<PlaceholderAnimation />
	);

	const blockLoaded = (
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
					src={ `${ getSiteUrl() }${ giveLogo }` }
					alt={ __( 'Give Icon' ) } />
			) }
			{ !! isLoader ? blockLoading : blockLoaded }
		</div>
	);
};

export default GiveBlankSlate;
