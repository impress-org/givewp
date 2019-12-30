import './style.scss'
import { getColor } from './utils'
import PropTypes from 'prop-types'

const Legend = ({data, chartRef}) => {

    const total = data.datasets[0].data.reduce((a, b) => a + b)

    const hideData = (evt, index, value) => {
        evt.target.closest('.item').classList.toggle('inactive')
        chartRef.data.datasets[0].data[index] = chartRef.data.datasets[0].data[index] === value ? null : value
        chartRef.update()
    }

    const items = data.labels.map((label, index) => {
        const color = getColor(index)
        const percent = Math.round( ( data.datasets[0].data[index] / total ) * 100 ) + '%'
        return (
            <div className='item' onClick={(evt) => hideData(evt, index, data.datasets[0].data[index])}>
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

Legend.propTypes = {
    // Data object provided from Reports API
    data: PropTypes.object.isRequired,
    // Chart object created and passed by parent Chart component
    chartRef: PropTypes.object.isRequired,
}

Legend.defaultProps = {
    data: null,
    chartRef: null
}

export default Legend