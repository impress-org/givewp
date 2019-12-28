import './style.scss'
import { getColor } from './utils'

const Legend = ({data}) => {

    const items = data.labels.map((label, index) => {
        const color = getColor(index)
        return (
            <div className='item'>
                <div className='bar' style={{background: color}}></div>
                <p>{label}</p>
                <p>{data.datasets[0].data[index]}</p>
            </div>
        )
    })
    return (
        <div className='givewp-legend'>
            {items}
        </div>
    )
}

export default Legend