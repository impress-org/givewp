/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { withSelect } = wp.data;
const { ServerSideRender } = wp.components;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import SelectForm from '../../components/select-form';
import Inspector from './inspector';
import Controls from './controls';

/**
 * Render Block UI For Editor
 */

const GiveForm = ( props ) => {
	const { attributes, forms, isSelected, className } = props;
	const { id } = attributes;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		if ( ! forms ) {
			blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
		} else if ( forms && forms.length === 0 ) {
			blockUI = <NoForms />;
		} else {
			blockUI = <SelectForm { ... { ...props } } />;
		}
	} else {
		blockUI = (
			<div id="donation-form-preview-block">
				<Inspector { ... { ...props } } />
				<Controls { ... { ...props } } />
				<ServerSideRender block="give/donation-form" attributes={ attributes } />
			</div>
		);
	}

	return (
		<div className={ !! isSelected ? `${ className } isSelected` : className } >
			{ blockUI }
		</div>
	);
};

/**
 * Export component with forms
 */
export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms' ),
	};
} )( GiveForm );
