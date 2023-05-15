import PropTypes from 'prop-types';
import './style.scss';

const Grid = ({gap, visible, children}) => {
    const display = visible ? 'grid' : 'none';

    //To do: swap with scss
    const gridStyle = {
        display: display,
        gridGap: gap,
    };

    return (
        <div className="givewp-grid" style={gridStyle}>
            {children}
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
