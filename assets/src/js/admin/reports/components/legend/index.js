import './style.scss'
import { getColor } from './utils'

const Legend = ({data}) => {

    const total = data.datasets[0].data.reduce((a, b) => a + b)

    const items = data.labels.map((label, index) => {
        const color = getColor(index)
        const percent = Math.round( ( data.datasets[0].data[index] / total ) * 100 ) + '%'
        return (
            <div className='item'>
                <div className='bar' style={{background: color}}></div>
                <p className='label'>{label}</p>
                <p className='data'>{data.datasets[0].data[index]}</p>
                <p className='percent'>{percent}</p>
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