import PropTypes from 'prop-types';
import classNames from 'classnames';

import styles from './styles.module.scss';

const { __ } = wp.i18n;

const Modal = ( { visible, type, children } ) => {
	const modalStyles = classNames( {
		[ styles.modal ]: true,
		[ styles.error ]: type === 'error',
		[ styles.warning ]: type === 'warning',
		[ styles.success ]: type === 'success',
	} );

	return (
		<div className={ classNames( { [ styles.overlay ]: visible } ) }>
			<div className={ styles.container }>
				<div className={ modalStyles }>
					<div className={ styles.content }>
						{ children }
					</div>
				</div>
			</div>
		</div>
	);
};

Modal.Title = ( { children } ) => {
	return (
		<div className={ styles.title }>
			{ children }
		</div>
	);
};

Modal.CloseIcon = ( { onClick } ) => {
	return (
		<div className={ styles.close } onClick={ onClick }>
			<span className="dashicons dashicons-no" />
		</div>
	);
};

Modal.Section = ( { title, content } ) => {
	return (
		<div className={ styles.section }>
			<strong>{ title }:</strong>
			{ content }
		</div>
	);
};

Modal.LogContext = ( { context } ) => {
	return (
		<div className={ styles.section }>
			<strong>{ __( 'Error details', 'give' ) }:</strong>
			<div className={ styles.errorDetailsContainer }>
				{ context && Object.entries( context ).map( ( [ key, value ] ) => {
					return (
						<div key={ key }>
							<strong>{ key }:</strong>
							{ value }
						</div>
					);
				} ) }
			</div>
		</div>
	);
};

Modal.propTypes = {
	// Is visible
	visible: PropTypes.bool.isRequired,
	// Is loading
	loading: PropTypes.bool,
	// Modal type
	type: PropTypes.string,
	// Collection of react DOM elements
	children: PropTypes.object,
	// Collection of react DOM elements
	onClose: PropTypes.func,
};

Modal.defaultProps = {
	visible: true,
	loading: false,
	type: 'notice',
	children: {},
	onClose: () => {},
};

export default Modal;
