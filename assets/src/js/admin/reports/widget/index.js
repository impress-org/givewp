import Grid from '../components/grid'
import Card from '../components/card'
import Chart from '../components/chart'

const Widget = () => {
    return (
        <Grid>
            <Card width={12}>
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
                        ]
                    }}
                />
            </Card>
            <Card width={6}>Mini Chart here</Card>
            <Card width={6}>Mini chart here</Card>
            <Card width={6}>Mini chart here</Card>
            <Card width={6}>Mini chart here</Card>
        </Grid>
    )
}
export default Widget