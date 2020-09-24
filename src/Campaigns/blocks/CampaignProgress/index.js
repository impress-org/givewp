const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, BaseControl, ColorPalette } = wp.components;

import ProgressBar from '../components/progress-bar';
import { Footer, FooterItem } from '../components/footer';

const defaultColor = '#66bb6a';

/* eslint-disable-next-line no-undef */
const editorColorPalette = giveCampaignsThemeSupport.editorColorPalette;

export default registerBlockType( 'give/campaign-progress', {
	title: __( 'Progress' ),
	description: __( '...' ),
	category: 'give',
	keywords: [
		// ...
	],
	supports: {
		html: false,
	},
	attributes: {
		color: {
			type: 'string',
			default: defaultColor,
		},
	},
	edit: ( { attributes, setAttributes } ) => {
		const { color } = attributes;

		return (
			<>
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Settings' ) }>
						<BaseControl
							label={ __( 'Progress Bar Color', 'give' ) }
						>
							<ColorPalette
								value={ color }
								colors={ editorColorPalette }
								onChange={ ( newValue ) => setAttributes( { color: newValue ?? defaultColor } ) }
								clearable={ true }
							/>
						</BaseControl>
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
