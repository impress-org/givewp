import ChartJS from 'chart.js'
import { useEffect, createRef } from 'react'
import { createConfig, calcHeight } from './utils'

const Chart = ({type, aspectRatio, data, showLegend}) => {

    const canvas = createRef()
    const config = createConfig(type, data, showLegend)
    const height = 100 * aspectRatio

    useEffect(() => {

        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        return function cleanup() {
            chart.destroy()
        }

    }, [height])

    return (
        <div>
            <canvas width={100} height={height}  ref={canvas}></canvas>
        </div>
    )
}
export default Chart