/**
 * WordPress dependencies
 */
const ServerSideRender = wp.serverSideRender;
const { withSelect } = wp.data;

/**
 * Render Block UI For Editor
 */

const Milestone = ( { attributes } ) => {
	return <ServerSideRender block="give/milestone" attributes={ attributes } />;
};

export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms' ),
	};
} )( Milestone );
