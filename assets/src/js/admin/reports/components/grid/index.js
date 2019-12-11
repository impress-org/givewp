const Grid = (props) => {

    const gridStyle = {
        display: 'grid',
        gridTemplateColumns: 'repeat(12, 1fr)',
        gridGap: '16px',
        marginTop: '26px',
    }

    return (
        <div style={gridStyle}>
            {props.children}
        </div>
    )
}
export default Grid