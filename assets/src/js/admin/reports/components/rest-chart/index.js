import qs from 'qs';
import axios from 'axios';
import Chart from '../chart'
import { useState, useEffect } from 'react'
import { useStoreValue } from '../../store';

const RESTChart = ({type, aspectRatio, endpoint, showLegend}) => {

	const [{ period }, dispatch] = useStoreValue()
	const [fetched, setFetched] = useState({
		labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul'],
		datasets: [
			{
				label: 'Donations',
				data: [4, 5, 3, 7, 5, 6]
			}
		]
	})

	useEffect(() => {

		console.log('period changed!', period)

		axios.get(wpApiSettings.root + 'give-api/v2/reports/payment-statuses', {
			params: {
				start: '2011-09-14',
				end: '2014-08-13'
			},
			headers: {
				'X-WP-Nonce': wpApiSettings.nonce
			}
		})
		.then(function (response) {
			console.log(response)
			setFetched(response.data.data)
		})

	}, [period, endpoint])

	return (
		<Chart
			type={type}
			aspectRatio={aspectRatio}
			data={fetched}
			showLegend={showLegend}
		/>
	)
}
export default RESTChart
