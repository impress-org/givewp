import ChartJS from 'chart.js'
import { useEffect, useState, createRef } from 'react'
import { createConfig, calcHeight } from './utils'
import PropTypes from 'prop-types'

import Legend from '../legend'

const Chart = ({type, aspectRatio, data, showLegend}) => {

    const canvas = createRef()
    const config = createConfig(type, data)
    const height = 100 * aspectRatio
    const [legend, setupLegend] = useState(null)

    useEffect(() => {

        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        showLegend && setupLegend(<Legend data={data} chartRef={chart}/>)

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
    type: PropTypes.string.isRequired,
    // Aspect ratio used to display chart (number:1)
    aspectRatio: PropTypes.number.isRequired
    // Data object provided by Reports API
    data: PropTypes.object.isRequired,
    // Whether or not to display a legend for Chart (default to true)
    showLegend: PropTypes.bool
}

export default Chart