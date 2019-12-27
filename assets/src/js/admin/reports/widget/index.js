import Grid from '../components/grid'
import Card from '../components/card'

const Widget = () => {
    return (
        <Grid>
            <Card width={12}>
                <h1>Testing</h1>
            </Card>
            <Card width={6}></Card>
            <Card width={6}></Card>
            <Card width={6}></Card>
            <Card width={6}></Card>
        </Grid>
    )
}
export default Widget