// Vendor dependencies
import axios from 'axios'
import { useState, useEffect, Fragment } from 'react'
import PropTypes from 'prop-types'

// Components
import List from '../list'
import DonorItem from '../donor-item'
import LocationItem from '../location-item'
import DonationItem from '../donation-item'

// Store-related dependencies
import { useStoreValue } from '../../app/store'

const RESTList = ({endpoint}) => {

	// Use period from store
	const [{ period }, dispatch] = useStoreValue()

	// Use state to hold data fetched from API
	const [fetched, setFetched] = useState(null)

	// Fetch new data and update List when period changes
	useEffect(() => {
		axios.get(wpApiSettings.root + 'give-api/v2/reports/' + endpoint, {
			params: {
				start: period.startDate.format('YYYY-MM-DD'),
				end: period.startDate.format('YYYY-MM-DD')
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

	const items = fetched.map((item, index) => {
		switch (item.type) {
			case 'donor':
				return <DonorItem name={item.name} />
			case 'donation':
				return <DonationItem name={item.name} />
			case 'location':
				return <LocationItem name={item.name} />
		}
	})

	return (
		<Fragment>
			{fetched && (
				<List>
					{items}
				</List>
			)}
		</Fragment>
	)
}

RESTList.propTypes = {
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired
}

RESTList.defaultProps = {
	endpoint: null,
}

export default RESTList
