// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../components/grid'
import Card from '../../components/card'
import Chart from '../../components/chart'

const OverviewPage = () => {
    return (
        <Grid>
            <Card
            title="Sample Chart">
                <Chart/>
            </Card>
        </Grid>
    )
}
export default OverviewPage