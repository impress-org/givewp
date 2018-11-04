/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * WordPress dependencies
 */
const { ServerSideRender } = wp.components;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveDonationFormGrid = ( props ) => {
	const { forms, className, attributes } = props;

	// Render block UI
	let blockUI;

	if ( ! forms ) {
		blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
	} else if ( forms && forms.length === 0 ) {
		blockUI = <NoForms />;
	} else {
		blockUI = (
			<div id="donation-form-grid-preview-block">
				<Inspector { ... { ...props } } />
				<ServerSideRender block="give/donation-form-grid" attributes={ attributes } />
			</div>
		);
	}

	return (
		<div className={ className } >
			{ blockUI }
		</div>
	);
};

export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms' ),
	};
} )( GiveDonationFormGrid );
