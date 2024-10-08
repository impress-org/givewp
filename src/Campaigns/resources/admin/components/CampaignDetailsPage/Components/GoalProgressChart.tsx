import {__} from '@wordpress/i18n';
import {
    CircularProgressbarWithChildren,
    buildStyles
} from "react-circular-progressbar";
import 'react-circular-progressbar/dist/styles.css';

const GoalProgressChart = ({ value, goal }) => {
    const percentage: number = Math.abs((value / goal) * 100);
    return (
        <>
            <div style={{
                display: 'grid',
                gridTemplateColumns: '2fr 3fr',
                gap: '20px',
                alignItems: 'center',
            }}>
                <div style={{
                    padding: '10px',
                }}>
                    <CircularProgressbarWithChildren
                        value={percentage}
                        styles={buildStyles({
                            strokeLinecap: "butt",
                            pathColor: "#459948",
                        })}
                    >
                        {percentage}%
                        <small>{value}</small>
                    </CircularProgressbarWithChildren>
                </div>
                <div>
                    <div>{__('Goal')}</div>
                    <div>{goal}</div>
                    <div>{__('Amount raised')}</div>
                </div>
            </div>
        </>
    )
}

export default GoalProgressChart;
