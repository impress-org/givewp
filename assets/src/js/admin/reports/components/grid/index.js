import PropTypes from 'prop-types';

const Grid = ( { gap, visible, children } ) => {
	const display = visible === true ? 'grid' : 'none';

	//To do: swap with scss
	const gridStyle = {
		display: display,
		gridTemplateColumns: 'repeat(12, 1fr)',
		gridGap: gap,
		marginTop: '30px',
	};

	return (
		<div style={ gridStyle }>
			{ children }
		</div>
	);
};

Grid.propTypes = {
	// Grid gap spacing (ex: 30px)
	gap: PropTypes.string,
	visible: PropTypes.bool,
	// Grid items
	children: PropTypes.node.isRequired,
};

Grid.defaultProps = {
	gap: '30px',
	visible: true,
	children: null,
};

export default Grid;
