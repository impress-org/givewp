// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../../components/grid'
import Card from '../../../components/card'
import Chart from '../../../components/chart'
import RESTChart from '../../../components/rest-chart'
import MiniChart from '../../../components/mini-chart'
import List from '../../../components/list'
import RESTList from '../../../components/rest-list'
import LocationItem from '../../../components/location-item'
import DonationItem from '../../../components/donation-item'
import DonorItem from '../../../components/donor-item'
const { __ } = wp.i18n;

const OverviewPage = () => {
    return (
        <Grid>
            <Card width={3}>
                <MiniChart
                    title='Mini Doughnut'
                    type='doughnut'
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [400.00, 532.00, 333.00, 72.56, 300.00, 422.22]
                            }
                        ]
                    }}
                />
            </Card>
            <Card width={3}>
                <MiniChart
                    title='Mini Line'
                    type='line'
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [400.00, 532.00, 333.00, 72.56, 390.23, 350.00]
                            }
                        ]
                    }}
                />
            </Card>
            <Card width={3}>
                <MiniChart
                    title='Mini Pie'
                    type='pie'
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [400.00, 532.00, 333.00, 72.56, 300.00, 450.30]
                            }
                        ]
                    }}
                />
            </Card>
            <Card width={3}>
                <MiniChart
                    title='Mini Bar'
                    type='bar'
                    data={{
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
                        datasets: [
                            {
                                label: 'Donations',
                                data: [400.00, 532.00, 333.00, 72.56, 300.00, 450.00]
                            }
                        ]
                    }}
                />
            </Card>
            <Card title={__('Payment Methods', 'give')} width={4}>
				<RESTChart
                    type='doughnut'
                    aspectRatio={0.6}
                    endpoint='payment-methods'
                    showLegend={true}
                />
            </Card>
            <Card title={__('Payment Statuses', 'give')} width={4}>
				<RESTChart
                    type='bar'
                    aspectRatio={1.2}
                    endpoint='payment-statuses'
                    showLegend={false}
                />
            </Card>
            <Card title={__('Form Performance (All Time)', 'give')} width={4}>
                <RESTChart
                    type='pie'
                    aspectRatio={0.6}
                    endpoint='form-performance'
                    showLegend={true}
                />
            </Card>
            <Card title={__('Donor List', 'give')} width={4}>
				<RESTList endpoint='top-donors' />
            </Card>
            <Card title={__('Location List', 'give')} width={4}>
                <List onScrollEnd={() => alert('reached end!')}>
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                    <LocationItem
                        city='Anacorts'
                        state='Washington'
                        country='United States'
                        flag='flag.png'
                        count='4 Donations'
                        total='$345.00'
                    />
                 </List>
            </Card>
            <Card title={__('Donation List', 'give')} width={4}>
                <List onScrollEnd={() => alert('reached end of list!')}>
                    <DonationItem
                        status='completed'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                    <DonationItem
                        status='completed'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                    <DonationItem
                        status='abandoned'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                    <DonationItem
                        status='refunded'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                    <DonationItem
                        status='completed'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                    <DonationItem
                        status='completed'
                        amount='$50.00'
                        time='2013-02-08 09:30'
                        donor={{name: 'Test Name', id: 456}}
                        source='Save the Whales'
                    />
                </List>
            </Card>
            <Card title={__('Line Chart', 'give')} width={12}>
				<RESTChart
                    type='line'
                    aspectRatio={0.4}
                    endpoint='payment-statuses'
                    showLegend={false}
                />
            </Card>
        </Grid>
    )
}
export default OverviewPage
