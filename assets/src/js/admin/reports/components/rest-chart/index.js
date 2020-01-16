// Vendor dependencies
import axios from 'axios'
import { useState, useEffect, Fragment } from 'react'
import PropTypes from 'prop-types'

// Components
import Chart from '../chart'

// Store-related dependencies
import { useStoreValue } from '../../app/store'

const RESTChart = ({type, aspectRatio, endpoint, showLegend}) => {

	// Use period from store
	const [{ period }, dispatch] = useStoreValue()

	// Use state to hold data fetched from API
	const [fetched, setFetched] = useState(null)

	// Fetch new data and update Chart when period changes
	useEffect(() => {
		if (period.startDate && period.endDate) {
			axios.get(wpApiSettings.root + 'give-api/v2/reports/' + endpoint, {
				params: {
					start: period.startDate.format('YYYY-MM-DD-HH'),
					end: period.endDate.format('YYYY-MM-DD-HH')
				},
				headers: {
					'X-WP-Nonce': wpApiSettings.nonce
				}
			})
			.then(function (response) {
				console.log(endpoint, response)
				setFetched(response.data.data)
			})
		}
	}, [period, endpoint])

	return (
		<Fragment>
			{fetched && (
				<Chart
					type={type}
					aspectRatio={aspectRatio}
					data={fetched}
					showLegend={showLegend}
				/>
			)}
		</Fragment>
	)
}

RESTChart.propTypes = {
	// Chart type (ex: line)
	type: PropTypes.string.isRequired,
	// Chart aspect ratio
	aspectRatio: PropTypes.number,
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
	// Display Chart with Legend
	showLegend: PropTypes.bool
}

RESTChart.defaultProps = {
	type: null,
	aspectRatio: 0.6,
	endpoint: null,
	showLegend: false
}

export default RESTChart
