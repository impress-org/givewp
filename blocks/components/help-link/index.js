/**
* WordPress dependencies
*/
const { __ } = wp.i18n;

/**
 * Render Help link
*/

const GiveHelpLink = () => {
	return (
		<p className="give-blank-slate__help">
			Need help? Get started with <a href="http://docs.givewp.com/give101/" target="_blank" rel="noopener noreferrer">{ __( 'GiveWP 101' ) }</a>
		</p>
	);
};

export default GiveHelpLink;
