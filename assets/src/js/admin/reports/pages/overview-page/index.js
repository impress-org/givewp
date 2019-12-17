// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../components/grid'
import Card from '../../components/card'
import Chart from '../../components/chart'

const OverviewPage = () => {
    return (
        <Grid>
            <Card title="Sample Chart" width={12}>
                <Chart
                    type='line'
                    aspectRatio={0.4}
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                data: [4, 5, 3, 7, 5, 6]
                            }
                        ]
                    }}
                />
            </Card>
        </Grid>
    )
}
export default OverviewPage