/**
* Block dependencies
*/
import GiveHelpLink from '../help-link/index';
import PlaceholderContainerAnimation from '../container-placeholder-animation/index';

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

	const block_loading = (
		<PlaceholderContainerAnimation />
	);

	const block_loaded = (
		<div className="block-loaded">
			{ !! title && ( <h2 className="give-blank-slate__heading">{ title }</h2> ) }
			{ !! description && ( <p className="give-blank-slate__message">{ description }</p> ) }
			{ children }
			{ ( !! helpLink && <GiveHelpLink /> ) }
		</div>
	);

	return (
		<div className="give-blank-slate">
			{ !! isLoader ? block_loading : block_loaded }
		</div>
	);
};

export default GiveBlankSlate;
