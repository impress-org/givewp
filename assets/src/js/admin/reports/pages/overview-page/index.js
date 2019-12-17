// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../components/grid'
import Card from '../../components/card'
import Chart from '../../components/chart'

const OverviewPage = () => {
    return (
        <Grid>
            <Card title="Doughnut Chart" width={4}>
                <Chart
                    type='doughnut'
                    aspectRatio={0.7}
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [4, 5, 3, 7, 5, 6]
                            }
                        ]
                    }}
                />
            </Card>
            <Card title="Bar Chart" width={4}>
                <Chart
                    type='bar'
                    aspectRatio={0.7}
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [4, 5, 3, 7, 5, 6]
                            }
                        ]
                    }}
                />
            </Card>
            <Card title="Pie Chart" width={4}>
                <Chart
                    type='pie'
                    aspectRatio={0.7}
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [4, 5, 3, 7, 5, 6]
                            }
                        ]
                    }}
                />
            </Card>
            <Card title="Line Chart" width={12}>
                <Chart
                    type='line'
                    aspectRatio={0.4}
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [4, 5, 3, 7, 5, 6]
                            },
                            // {
                            //     label: 'Refunds',
                            //     data: [2, 4, 6, 4, 3, 4]
                            // }
                        ]
                    }}
                />
            </Card>
        </Grid>
    )
}
export default OverviewPage