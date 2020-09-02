/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import GiveLogo from '../components/logo';
import Milestone from './edit';

/**
 * Required styles (both common and editor styles)
 */
import './common.scss';
import './editor.scss';

/**
 * Register Block
 */

export default registerBlockType( 'give/milestone', {
	title: __( 'Milestone', 'give' ),
	description: __( 'The GiveWP Milestone block inserts an progress bar indicating donations raised across multiple forms. Each Milestone\'s presentation can be customized below.' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donation', 'give' ),
		__( 'milestone', 'give' ),
	],
	attributes: blockAttributes,
	edit: Milestone,
} );
