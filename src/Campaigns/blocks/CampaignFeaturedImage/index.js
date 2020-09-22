const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { PostFeaturedImage } = wp.editor;

wp.hooks.addFilter( 'editor.PostFeaturedImage.imageSize', 'give/campaigns', function() {
	return 'large';
} );

export default registerBlockType( 'give/campaign-featured-image', {
	title: __( 'Campaign Featured Image' ),
	edit: () => {
		return <PostFeaturedImage />;
	},
} );
