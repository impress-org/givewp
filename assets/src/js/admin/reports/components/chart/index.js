import ChartJS from 'chart.js'
import { useEffect, useState, createRef } from 'react'
import { createConfig, calcHeight } from './utils'

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
export default Chart