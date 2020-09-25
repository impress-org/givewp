const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody } = wp.components;

import ProgressBar from '../../components/progress-bar';
import { Footer, FooterItem } from '../../components/footer';
import ColorControl from '../../components/color-control';

export default registerBlockType( 'give/progress-bar', {
	title: __( 'Progress Bar' ),
	description: __( '...' ),
	category: 'give',
	parent: [ 'give/multi-form-goal' ],
	attributes: {
		color: {
			type: 'string',
			default: '#66bb6a',
		},
	},
	edit: ( { attributes, setAttributes } ) => {
		const { color } = attributes;

		const saveSetting = ( name, value ) => {
			setAttributes( {
				[ name ]: value,
			} );
		};

		return (
			<>
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Settings' ) }>
						<ColorControl
							name="color"
							label={ __( 'Progress Bar Color', 'give' ) }
							onChange={ ( value ) => saveSetting( 'color', value ) }
							value={ 'red' }
						/>
					</PanelBody>
				</InspectorControls>
				<div style={ { padding: '20px 10px' } }>
					<ProgressBar percent={ 33 } color={ color } />
				</div>
				<Footer>
					<FooterItem title="$3,000" subtitle="raised!" />
					<FooterItem title="50" subtitle="donations" />
					<FooterItem title="$10,000" subtitle="goal" />
					<FooterItem title="30" subtitle="days to go" />
				</Footer>
			</>
		);
	},
	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
