import { createRef, useEffect } from 'react'
import ChartJS from 'chart.js'
import { createConfig } from './utils'
import './style.scss'

const MiniChart = ({title, data}) => {

    const amount = '$200.00'
    const percentage = -5

    const canvas = createRef()
    const config = createConfig(data)

    useEffect(() => {

        const ctx = canvas.current.getContext('2d')
        const chart = new ChartJS(ctx, config)

        return function cleanup() {
            chart.destroy()
        }

    }, [])


    return (
        <div className='givewp-mini-chart'>
            <div className='header'>
                <div className='title'>{title}</div>
                <div className='percentage'>{percentage}</div>
            </div>
            <div className='content'>
                <div className='amount'>{amount}</div>
                <canvas width={100} height={25}  ref={canvas}></canvas>
            </div>
        </div>
    )
}

export default MiniChart