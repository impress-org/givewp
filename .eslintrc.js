/**
 * ESLint configuration for GiveWP.
 *
 * The project does not ship a full @wordpress/eslint-plugin recommended preset;
 * those plugins resolve only under @wordpress/scripts' nested node_modules and
 * cannot be referenced from a root config without hoisting the whole tree.
 * This config intentionally scopes ESLint to the single coding standard that
 * needs enforcing today: React runtime APIs must be imported from
 * "@wordpress/element", not "react". Type-only imports remain allowed for the
 * React types @wordpress/element does not re-export (ReactNode, FC, ...).
 *
 * @unreleased
 * @see https://github.com/impress-org/givewp/issues/6988
 */
module.exports = {
	root: true,
	parser: '@typescript-eslint/parser',
	parserOptions: {
		ecmaVersion: 'latest',
		sourceType: 'module',
		ecmaFeatures: { jsx: true },
	},
	plugins: [ '@typescript-eslint' ],
	rules: {
		'@typescript-eslint/no-restricted-imports': [ 'error', {
			paths: [
				{
					name: 'react',
					message: 'Import React runtime APIs (hooks, components) from "@wordpress/element" instead. Type-only imports (import type {...} from \'react\') are allowed for types @wordpress/element does not re-export, such as ReactNode, FC, CSSProperties, and ChangeEvent.',
					allowTypeImports: true,
				},
			],
		} ],
	},
};
