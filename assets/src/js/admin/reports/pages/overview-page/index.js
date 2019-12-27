// Overview Page component
// Pages use the Grid component to establish a
// 12 column grid for content to exist in

import Grid from '../../components/grid'
import Card from '../../components/card'
import Chart from '../../components/chart'
import List from '../../components/list'
import LocationItem from '../../components/location-item'
import DonationItem from '../../components/donation-item'
import DonorItem from '../../components/donor-item'
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
            <Card title={__('Donor List', 'give')} width={4}>
                <List onScrollEnd={() => alert('Reached end!')} >
                    <DonorItem
                        image={null}
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        name='Test A Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        name='Test'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        name='Does This Work'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                    <DonorItem
                        image='./default.png'
                        name='Test Name'
                        email='email@test.org'
                        count='2 Donations'
                        total='$200.00'
                    />
                </List>
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