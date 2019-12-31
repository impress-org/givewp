import PropTypes from 'prop-types'

const Grid = ({gap}) => {

    //To do: swap with scss
    const gridStyle = {
        display: 'grid',
        gridTemplateColumns: 'repeat(12, 1fr)',
        gridGap: gap,
        marginTop: '30px',
    }

    return (
        <div style={gridStyle}>
            {props.children}
        </div>
    )
}

Grid.propTypes = {
    // Grid gap spacing (ex: 30px)
    gap: PropTypes.string
}

Grid.defaultProps = {
    gap: '30px'
}

export default Grid