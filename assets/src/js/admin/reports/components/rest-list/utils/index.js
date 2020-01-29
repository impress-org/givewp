import DonationItem from '../../donation-item';
import DonorItem from '../../donor-item';
import LocationItem from '../../location-item';

// Get color associated with a specific index
export function getItems( data ) {
	const items = Array.isArray( data ) && data.length ? data.map( ( item, index ) => {
		switch ( item.type ) {
			case 'donor':
				return <DonorItem
					image={ item.image }
					name={ item.name }
					email={ item.email }
					count={ item.count }
					total={ item.total }
					key={ index }
				/>;
			case 'donation':
				return <DonationItem
					status={ item.status }
					amount={ item.amount }
					time={ item.time }
					donor={ item.donor }
					source={ item.source }
					key={ index }
				/>;
			case 'location':
				return <LocationItem
					city={ item.city }
					state={ item.state }
					country={ item.country }
					flag={ item.flag }
					count={ item.count }
					total={ item.total }
					key={ index }
				/>;
		}
	} ) : null;

	return items;
}

export function getSkeletonItems() {
	const data = [
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
		{
			type: 'donor',
			name: '- -',
			email: '--',
			count: '--',
			total: '--',
		},
	];
	const items = getItems( data );
	return items;
}
