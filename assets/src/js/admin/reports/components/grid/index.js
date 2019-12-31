import PropTypes from 'prop-types'

const Grid = ({gap, children}) => {

    //To do: swap with scss
    const gridStyle = {
        display: 'grid',
        gridTemplateColumns: 'repeat(12, 1fr)',
        gridGap: gap,
        marginTop: '30px',
    }

    return (
        <div style={gridStyle}>
            {children}
        </div>
    )
}

Grid.propTypes = {
    // Grid gap spacing (ex: 30px)
    gap: PropTypes.string,
    // Grid items
    children: PropTypes.node.isRequired
}

Grid.defaultProps = {
    gap: '30px',
    children: null
}

export default Grid