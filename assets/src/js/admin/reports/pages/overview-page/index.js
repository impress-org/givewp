// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../components/grid'
import Card from '../../components/card'
import Chart from '../../components/chart'
import List from '../../components/list'
const { __ } = wp.i18n;

const OverviewPage = () => {
    return (
        <Grid>
            <Card title={__('Dougnhut Chart', 'give')} width={4}>
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
            <Card title={__('Bar Chart', 'give')} width={4}>
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
            <Card title={__('Pie Chart', 'give')} width={4}>
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
            <Card title={__('Example List', 'give')} width={3}>
                <List onApproachScrollEnd={() => console.log('approaching end!')} >
                    <div style={{height: '200px', background: '#CCCCCC'}} />
                </List>
            </Card>
            <Card title={__('Line Chart', 'give')} width={12}>
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