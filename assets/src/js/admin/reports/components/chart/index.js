import ChartJS from 'chart.js'
import { useEffect, useLayoutEffect, useState, createRef } from 'react'
import { createConfig } from './utils'

const Chart = ({type, data}) => {

    const canvas = createRef()
    const config = createConfig(type, data)
    const [height, setHeight] = useState(100)

    useEffect(() => {

        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        return function cleanup() {
            chart.destroy()
        }

    }, [])

    useLayoutEffect(() => {

        function updateHeight (evt) {
            console.log('update height!', canvas)
            setHeight(80)
        }

        window.addEventListener('resize', updateHeight)
        updateHeight()

        return function cleanup() {
            window.removeEventListener('resize', updateHeight)
        }

    }, [])

    return (
        <div>
            <canvas width={100} height={height}  ref={canvas}></canvas>
        </div>
    )
}
export default Chart