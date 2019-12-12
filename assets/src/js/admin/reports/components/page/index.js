import Grid from '../grid'
import Card from '../card'
import Chart from '../chart'
import List from '../list'
import Table from '../table'

const Page = (props) => {
    const page = props.page

    const cards = Object.values(page.cards).map((card, index) => {

        let content
        switch (card.type) {
            case 'chart':
                content = <Chart setup={card.props}/>
                break;
            case 'list':
                content = <List/>
                break;
            case 'table':
                content = <Table/>
                break;
        }

        return (
            <Card title={card.title} width={card.width} key={index}>
                <p>Type: {card.type}</p>
                <p>{JSON.stringify(card.props)}</p>
                {content}
            </Card>
        )
    })

    return (
        <div>
            <Grid>
                {cards}
            </Grid>
        </div>
    )
}
export default Page