/**
 * WordPress dependencies
 */
const ServerSideRender = wp.serverSideRender;

/**
 * Internal dependencies
 */
import './block.scss';
import SelectForm from '../../components/select-form';
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveForm = ( props ) => {
	const { attributes, isSelected, className } = props;
	const { id } = attributes;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		blockUI = <SelectForm { ... { ...props } } />;
	} else {
		blockUI = (
			<div className={ !! isSelected ? `${ className } isSelected` : className } >
				<Inspector { ... { ...props } } />
				<ServerSideRender block="give/donation-form" attributes={ attributes } />
			</div>
		);
	}

	return blockUI;
};

export default GiveForm;
