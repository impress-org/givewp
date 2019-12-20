import './style.scss'

const Card = ({width, title, children}) => {
    return (
        <div className='givewp-card' style={{gridColumn: 'span ' + width}}>
            <div className='title'>
                {title}
            </div>
            <div className='content'>
                {children}
            </div>
        </div>
    )
}
export default Card