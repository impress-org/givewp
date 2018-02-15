/**
* Internal dependencies
* Using target="_blank" without rel="noopener noreferrer" is a security risk: see https://mathiasbynens.github.io/rel-noopener (react/jsx-no-target-blank)
*/

const GiveHelpLink = () => {
	return (
		<p className="give-blank-slate__help">
			Need help ? Get started with <a href="http://docs.givewp.com/give101/" target="_blank" rel="noopener noreferrer">Give 101</a>
		</p>
	);
};

export default GiveHelpLink;
