import Chart from '../chart'
import './style.scss'

const MiniChart = ({title, type, data}) => {

    const amount = '$200.00'
    const percentage = -5

    return (
        <div className='givewp-mini-chart'>
            <div className='header'>
                <div className='title'>{title}</div>
                <div className='percentage'>{percentage}</div>
            </div>
            <div className='content'>
                <div className='amount'>{amount}</div>
                <Chart type={type} data={data} aspectRatio={1} />
            </div>
        </div>
    )
}

export default MiniChart