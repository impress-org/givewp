/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import GiveLogo from '../../components/logo';
import ProgressBar from './edit';

/**
 * Required styles (both common and editor styles)
 */
import '../../../css/common.scss';
import '../../../css/editor.scss';

/**
 * Register Block
 */

export default registerBlockType( 'give/progress-bar', {
	title: __( 'Progress Bar', 'give' ),
	description: __( 'The Progress Bar block displays progress made across donation forms towards a common goal.', 'give' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'progress-bar', 'give' ),
	],
	attributes: blockAttributes,
	parent: [ 'give/multi-form-goal' ],
	edit: ProgressBar,
} );
