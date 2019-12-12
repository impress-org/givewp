const Grid = (props) => {

    //To do: swap with scss
    const gridStyle = {
        display: 'grid',
        gridTemplateColumns: 'repeat(12, 1fr)',
        gridGap: '30px',
        marginTop: '30px',
    }

    return (
        <div style={gridStyle}>
            {props.children}
        </div>
    )
}
export default Grid