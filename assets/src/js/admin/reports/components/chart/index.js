import ChartJS from 'chart.js'
import { useEffect, createRef } from 'react'
import { createConfig } from './utils'

const Chart = ({type, data}) => {

    const canvas = createRef()
    const config = createConfig(type, data)
    let height = 100

    useEffect(() => {

        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        return function cleanup() {
            chart.destroy()
        }

    }, [])

    return (
        <div>
            <canvas width={100} height={height}  ref={canvas}></canvas>
        </div>
    )
}
export default Chart