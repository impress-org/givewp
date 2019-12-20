import PropTypes from 'prop-types'
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

Card.propTypes = {
    width: PropTypes.number,
    title: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired
}

Card.defaultProps = {
    width: 4,
    title: null,
    children: null
}

export default Card