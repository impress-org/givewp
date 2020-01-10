// Dependencies
import PropTypes from 'prop-types'
import { useState } from 'react'

// Utilities
import { getColor } from './utils'

// Styles
import './style.scss'

const Legend = ({data, chartRef}) => {

    // Calculate total value of points in dataset (used to find percentages)
	const total = data.datasets[0].data.reduce((a, b) => parseInt(a) + parseInt(b))

    const [inactiveItems, setInactiveItems] = useState([])

    // Hide a given datapoint in the chart and the coresponding legend item
    const hideData = (index, value) => {
        let inactive = inactiveItems.slice(0)
        console.log('hide data!')
        if (chartRef.data.datasets[0].data[index] === value) {
            chartRef.data.datasets[0].data[index] = null
            inactive.push(index)
            setInactiveItems(inactive)
        } else {
            chartRef.data.datasets[0].data[index] = value
            const foundIndex = inactive.indexOf(index)
            inactive.splice(foundIndex, 1)
            setInactiveItems(inactive)
        }
        //chartRef.data.datasets[0].data[index] = chartRef.data.datasets[0].data[index] === value ? null : value
        chartRef.update()
    }

    // Map data labels to build legend items
    const items = data.labels.map((label, index) => {

        // Prepare status class for item
        const status = inactiveItems.includes(index) ? ' inactive' : ''

        // Use ulitity function to match item color to chart color
        const color = getColor(index)

        // Prepare percent to display with legend item
        const percent = Math.round( ( data.datasets[0].data[index] / total ) * 100 ) + '%'

        return (
            <div className={'item' + status} key={index} onClick={() => hideData(index, data.datasets[0].data[index])}>
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
