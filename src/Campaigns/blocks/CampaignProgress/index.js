const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { useEntityProp } = wp.coreData;
const { useInstanceId } = wp.compose;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, BaseControl, ColorPalette } = wp.components;

import ProgressBar from '../components/progress-bar';
import { Footer, FooterItem } from '../components/footer';
import GoalAmountSetting from '../components/goal-amount-setting';

export default registerBlockType( 'give/campaign-progress', {
	title: __( 'Progress' ),
	description: __( '' ),
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
			default: '#66bb6a',
		},
	},
	edit: ( { attributes, setAttributes } ) => {
		const postType = useSelect(
			( select ) => select( 'core/editor' ).getCurrentPostType(),
			[]
		);
		/* eslint-disable-next-line no-unused-vars */
		const [ meta, setMeta ] = useEntityProp(
			'postType',
			postType,
			'meta'
		);

		/* eslint-disable-next-line no-undef */
		const goalAmount = Give.fn.formatCurrency( meta[ 'goal_amount' ], { precision: 0 } );

		const { color } = attributes;

		const saveSetting = ( name, value ) => {
			setAttributes( {
				[ name ]: value,
			} );
		};

		const ColorControl = ( { name, label, help, className, value, hideLabelFromVision } ) => {
			const instanceId = useInstanceId( ColorControl );
			const id = `give-color-control-${ name }-${ instanceId }`;
			const colors = [
				{ name: __( 'Red', 'give' ), color: '#dd3333' },
				{ name: __( 'Orange', 'give' ), color: '#dd9933' },
				{ name: __( 'Green', 'give' ), color: '#28C77B' },
				{ name: __( 'Blue', 'give' ), color: '#1e73be' },
				{ name: __( 'Purple', 'give' ), color: '#8224e3' },
				{ name: __( 'Grey', 'give' ), color: '#777777' },
			];
			return (
				<>
					<GoalAmountSetting />
					<BaseControl
						label={ label }
						hideLabelFromVision={ hideLabelFromVision }
						id={ id }
						help={ help }
						className={ className }
					>
						<ColorPalette
							value={ value }
							colors={ colors }
							onChange={ ( newValue ) => saveSetting( 'color', newValue ) }
							clearable={ false }
						/>
					</BaseControl>
				</>
			);
		};

		return (
			<>
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Settings' ) }>
						<ColorControl
							name="color"
							label={ __( 'Progress Bar Color', 'give' ) }
							// onChange={ ( value ) => {} }
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
					<FooterItem title={ '$' + goalAmount } subtitle="goal" />
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
