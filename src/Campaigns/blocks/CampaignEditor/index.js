const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks } = wp.blockEditor;
const { PanelBody } = wp.components;

const CardFooter = ( props ) => {
	const styles = {
		footer: {
			fontSize: '14px',
			color: '#767676',
			fontWeight: 600,
			borderTop: '1px solid #F2F2F2',
			backgroundColor: '#FBFBFB',
			borderRadius: '0 0 8px 8px',
		},
		footerStrong: {
			fontSize: '18px',
			color: '#4C4C4C',
		},
		footerLayout: {
			display: 'flex',
		},
		footerLayoutItem: {
			flex: 1,
			padding: '20px',
			borderRadius: '0 0 8px 8px',
			border: '1px solid #F2F2F2',
		},
	};

	return (
		<footer style={ styles.footer }>
			<div style={ styles.footerLayout }>
				{ props.children }
			</div>
		</footer>
	);
};

const FooterItem = ( props ) => {
	const styles = {
		container: {
			flex: 1,
			padding: '15px',
			fontSize: '18px',
			textAlign: 'center',
			borderRadius: '0 0 8px 8px',
			lineHeight: '29px',
		},
		strong: {
			fontSize: '24px',
			color: '#4C4C4C',
		},
	};
	return (
		<div style={ styles.container }>
			<strong style={ styles.strong }>{ props.title }</strong>
			<br />{ props.subtitle }
		</div>
	);
};

export default registerBlockType( 'give/campaign-editor', {
	title: __( 'Campaign Editor' ),
	description: __( '...' ),
	category: 'give',
	keywords: [
		// ...
	],
	supports: {
		html: false,
	},
	edit: () => {
		return (
			<>
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Settings' ) }>
						HERE
					</PanelBody>
				</InspectorControls>
				<InnerBlocks templateLock="all" />
				<CardFooter>
					<FooterItem title="$3,000" subtitle="raised" />
					<FooterItem title="50" subtitle="donations" />
					<FooterItem title="$10,000" subtitle="goal" />
					<FooterItem title="30" subtitle="days to go" />
				</CardFooter>
			</>
		);
	},
	save: () => {
		// Server side rendering via shortcode
		return <InnerBlocks.Content />;
	},
} );
