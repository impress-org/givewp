// Import vendor dependencies
import { useStoreValue } from '../../app/store';

import { __ } from '@wordpress/i18n'

// Import styles
import './style.scss';
import { goToStep } from '../../app/store/actions';

const SkipLink = () => {

	const [ { currentStep }, dispatch ] = useStoreValue();

	return (
		<a className="give-obw-skip-link" href="#"
		   onClick={ () => { dispatch( goToStep( currentStep + 1 ) ); } }
		>
			{ __( 'Skip joining community', 'give' ) }
		</a>
	);
};

export default SkipLink;
