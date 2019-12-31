// Dependencies
import ChartJS from 'chart.js'
import { useEffect, useState, createRef } from 'react'
import PropTypes from 'prop-types'

// Utilities
import { createConfig, calcHeight } from './utils'

// Components
import Legend from '../legend'

const Chart = ({type, aspectRatio, data, showLegend}) => {

    const canvas = createRef()
    const config = createConfig(type, data)
    const height = 100 * aspectRatio
    const [legend, setupLegend] = useState(null)

    useEffect(() => {

        // Setup chart
        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        // Setup legend
        showLegend && setupLegend(<Legend data={data} chartRef={chart}/>)

        // Cleanup chart
        return function cleanup() {
            chart.destroy()
        }

    }, [height])

    return (
        <div>
            <canvas width={100} height={height}  ref={canvas}></canvas>
            {legend}
        </div>
    )
}

Chart.propTypes = {
    // Chart type (see https://www.chartjs.org/docs/2.8.0/charts/)
    type: PropTypes.string,
    // Aspect ratio used to display chart (default 0.6)
    aspectRatio: PropTypes.number,
    // Data object provided by Reports API
    data: PropTypes.object.isRequired,
    // Whether or not to display a legend for Chart (default to false)
    showLegend: PropTypes.bool
}

Chart.defaultProps = {
    type: 'bar',
    aspectRatio: 0.6,
    data: null,
    showLegend: false
}

export default Chart