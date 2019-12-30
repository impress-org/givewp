// Dependencies
import PropTypes from 'prop-types'

// Utilities
import { getColor } from './utils'

// Styles
import './style.scss'

const Legend = ({data, chartRef}) => {

    // Calculate total value of points in dataset (used to find percentages)
    const total = data.datasets[0].data.reduce((a, b) => a + b)

    // Hide a given datapoint in the chart and the coresponding legend item
    const hideData = (evt, index, value) => {
        evt.target.closest('.item').classList.toggle('inactive')
        chartRef.data.datasets[0].data[index] = chartRef.data.datasets[0].data[index] === value ? null : value
        chartRef.update()
    }

    // Map data labels to build legend items
    const items = data.labels.map((label, index) => {

        // Use ulitity function to match item color to chart color
        const color = getColor(index)

        // Prepare percent to display with legend item
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