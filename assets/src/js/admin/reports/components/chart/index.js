import ChartJS from 'chart.js'
import { useEffect, createRef } from 'react'

const Chart = (props) => {
    const setup = props.setup
    const canvas = createRef()
    useEffect(() => {
        const ctx = canvas.current.getContext('2d')
        console.log('ctx!', ctx)
        const chart = new ChartJS(ctx, {
            type: setup.type,
            data: setup.data,
            options: setup.options
        })
    }, [])

    const calcHeight = setup.type === 'doughnut' || setup.type === 'pie' ? 100 : 40
    return (
        <div>
            <canvas width={100} height={calcHeight}  ref={canvas}></canvas>
        </div>
    )
}
export default Chart