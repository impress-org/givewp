import PropTypes from 'prop-types';
import './style.scss';

const Card = ( { width, title, children } ) => {
	return (
		<div className="givewp-card" style={ { gridColumn: 'span ' + width } }>
			{ title && ( <div className="title">
				{ title }
			</div> ) }
			<div className="content">
				{ children }
			</div>
		</div>
	);
};

Card.propTypes = {
	// Number of grid columns for Card to span, out of 12
	width: PropTypes.number,
	// Title of card
	title: PropTypes.string,
	// Elements to displayed in content area of card (eg: Chart, List)
	children: PropTypes.node.isRequired,
};

Card.defaultProps = {
	width: 4,
	title: null,
	children: null,
};

export default Card;
