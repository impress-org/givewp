/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { withSelect } = wp.data;
const { Placeholder, Spinner } = wp.components;

/**
 * Internal dependencies
 */
import './index.scss';
import GiveBlankSlate from '../blank-slate';
import NoForms from '../no-form';
import ChosenSelect from '../chosen-select';
import { getFormOptions } from '../../utils';

const SelectForm = ( { forms, setAttributes } ) => {
	const setFormIdTo = id => {
		setAttributes( { id: Number( id ) } );
	};

	// Render Component UI
	let componentUI;

	if ( ! forms ) {
		componentUI = <Placeholder><Spinner /></Placeholder>;
	} else if ( forms && forms.length === 0 ) {
		componentUI = <NoForms />;
	} else {
		componentUI = (
			<GiveBlankSlate title={ __( 'Donation Form' ) }>
				<ChosenSelect
					className="give-blank-slate__select"
					options={ getFormOptions( forms ) }
					onChange={ setFormIdTo }
					value={ 0 }
				/>
			</GiveBlankSlate>
		);
	}

	return componentUI;
};

/**
 * Export with forms data
 */
export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms', { per_page: 30 } ),
	};
} )( SelectForm );
