import { createRef, useEffect, Fragment } from 'react'
import PropTypes from 'prop-types'

//Import ChartJS dependencies
import ChartJS from 'chart.js'
import { createConfig, getPercentage, getAmount } from './utils'

import './style.scss'

const MiniChart = ({title, data}) => {

    //SVG up/positive icon
    const up = <div className='up'><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7 13C10.3137 13 13 10.3137 13 7C13 3.68629 10.3137 1 7 1C3.68629 1 1 3.68629 1 7C1 10.3137 3.68629 13 7 13Z" fill="#69B868" fill-opacity="0.3"/>
        <mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="3" y="4" width="8" height="5">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7 4.6665L3.5 8.1665L4.3225 8.989L7 6.31734L9.6775 8.989L10.5 8.1665L7 4.6665Z" fill="white"/>
        </mask>
        <g mask="url(#mask0)">
        <rect x="-7.5835" y="-7.5835" width="29.1667" height="29.1667" fill="#69B868"/>
        </g>
    </svg></div>

    //SVG down/negative icon
    const down = <div className='down'><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M7 13C10.3137 13 13 10.3137 13 7C13 3.68629 10.3137 1 7 1C3.68629 1 1 3.68629 1 7C1 10.3137 3.68629 13 7 13Z" fill="#D75A4B" fill-opacity="0.3"/>
        <mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="3" y="5" width="8" height="5">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.6775 5.01074L7 7.68241L4.3225 5.01074L3.5 5.83324L7 9.33324L10.5 5.83324L9.6775 5.01074Z" fill="white"/>
        </mask>
        <g mask="url(#mask0)">
        <rect x="-7.5835" y="-7.5835" width="29.1667" height="29.1667" fill="#D75A4B"/>
        </g>
    </svg></div>
    
    const amount = getAmount(data)
    const percentage = getPercentage(data)
    const indicator = Math.sign(percentage) === -1 ? <Fragment>{down} <span style={{color: '#D75A4B'}}>{Math.abs(percentage) + '%'}</span></Fragment> : <Fragment>{up} <span style={{color: '#69B868'}}>{Math.abs(percentage) + '%'}</span></Fragment>

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
                <div className='indicator'>{indicator}</div>
            </div>
            <div className='content'>
                <div className='amount'>{amount}</div>
                <div className='chart'>
                    <canvas width={100} height={40}  ref={canvas}></canvas>
                </div>
            </div>
        </div>
    )
}

MiniChart.propTypes = {
    //Chart title
    title: PropTypes.string.isRequired,
    //Chart data
    data: PropTypes.object.isRequired
}

MiniChart.defaultProps = {
    title: null,
    data: null
}

export default MiniChart