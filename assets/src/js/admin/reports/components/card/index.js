import './style.scss'

const Card = (props) => {
    return (
        <div className='givewp-card' style={{gridColumn: 'span ' + props.width}}>
            <div className='title'>
                {props.title}
            </div>
            <div className='content'>
                {props.children}
            </div>
        </div>
    )
}
export default Card