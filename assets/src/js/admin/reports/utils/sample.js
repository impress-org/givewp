export const getSampleData = ( endpoint ) => {
	switch ( endpoint ) {
		case 'income':
			return {
				datasets: [
					{
						data: [
							{
								y: 20,
								x: '2001-01-01',
							},
							{
								y: 88,
								x: '2001-01-02',
							},
							{
								y: 88,
								x: '2001-01-03',
							},
							{
								y: 88,
								x: '2001-01-04',
							},
							{
								y: 88,
								x: '2001-01-05',
							},
							{
								y: 88,
								x: '2001-01-06',
							},
							{
								y: 88,
								x: '2001-01-07',
							},
						],
						tooltips: [
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 1',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 2',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 3',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 4',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 5',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 6',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 7',
							},
						],
					},
				],
			};
		case 'total-income':
			return {
				datasets: [
					{
						trend: -5,
						highlight: '$150.00',
						info: 'VS previous 7 days',
						data: [
							{
								y: 20,
								x: '2001-01-01',
							},
							{
								y: 88,
								x: '2001-01-02',
							},
							{
								y: 88,
								x: '2001-01-03',
							},
							{
								y: 88,
								x: '2001-01-04',
							},
							{
								y: 88,
								x: '2001-01-05',
							},
							{
								y: 88,
								x: '2001-01-06',
							},
							{
								y: 88,
								x: '2001-01-07',
							},
						],
						tooltips: [
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 1',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 2',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 3',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 4',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 5',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 6',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 7',
							},
						],
					},
				],
			};
		case 'total-donors':
			return {
				datasets: [
					{
						trend: -5,
						highlight: '4',
						info: 'VS previous 7 days',
						data: [
							{
								y: 20,
								x: '2001-01-01',
							},
							{
								y: 88,
								x: '2001-01-02',
							},
							{
								y: 88,
								x: '2001-01-03',
							},
							{
								y: 88,
								x: '2001-01-04',
							},
							{
								y: 88,
								x: '2001-01-05',
							},
							{
								y: 88,
								x: '2001-01-06',
							},
							{
								y: 88,
								x: '2001-01-07',
							},
						],
						tooltips: [
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 1',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 2',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 3',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 4',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 5',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 6',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 7',
							},
						],
					},
				],
			};
		case 'average-donation':
			return {
				datasets: [
					{
						trend: -5,
						highlight: '$88.00',
						info: 'VS previous 7 days',
						data: [
							{
								y: 20,
								x: '2001-01-01',
							},
							{
								y: 88,
								x: '2001-01-02',
							},
							{
								y: 88,
								x: '2001-01-03',
							},
							{
								y: 88,
								x: '2001-01-04',
							},
							{
								y: 88,
								x: '2001-01-05',
							},
							{
								y: 88,
								x: '2001-01-06',
							},
							{
								y: 88,
								x: '2001-01-07',
							},
						],
						tooltips: [
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 1',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 2',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 3',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 4',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 5',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 6',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 7',
							},
						],
					},
				],
			};
		case 'total-refunds':
			return {
				datasets: [
					{
						trend: -5,
						highlight: '9',
						info: 'VS previous 7 days',
						data: [
							{
								y: 20,
								x: '2001-01-01',
							},
							{
								y: 88,
								x: '2001-01-02',
							},
							{
								y: 88,
								x: '2001-01-03',
							},
							{
								y: 88,
								x: '2001-01-04',
							},
							{
								y: 88,
								x: '2001-01-05',
							},
							{
								y: 88,
								x: '2001-01-06',
							},
							{
								y: 88,
								x: '2001-01-07',
							},
						],
						tooltips: [
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 1',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 2',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 3',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 4',
							},
							{
								title: '$10.00',
								body: '12 Donors',
								footer: 'Jan 5',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 6',
							},
							{
								title: '$20.00',
								body: '3 Donors',
								footer: 'Jan 7',
							},
						],
					},
				],
			};
		case 'payment-methods':
			return {
				datasets: [
					{
						data: [ 3, 5 ],
						tooltips: [
							{
								title: '$12,000',
								body: '44 Donors',
								footer: 'PayPal',
							},
							{
								title: '$4,000',
								body: '468 Donors',
								footer: 'Stripe',
							},
						],
					},
				],
			};
		case 'payment-statuses':
			return {
				labels: [
					'PayPal',
					'Stipe',
				],
				datasets: [
					{
						data: [ 3, 5 ],
						tooltips: [
							{
								title: '$12,000',
								body: '44 Donors',
								footer: 'PayPal',
							},
							{
								title: '$4,000',
								body: '468 Donors',
								footer: 'Stripe',
							},
						],
					},
				],
			};
		case 'form-performance':
			return {
				datasets: [
					{
						data: [ 3, 5 ],
						tooltips: [
							{
								title: '$12,000',
								body: '44 Donors',
								footer: 'PayPal',
							},
							{
								title: '$4,000',
								body: '468 Donors',
								footer: 'Stripe',
							},
						],
					},
				],
			};
		case 'recent-donations':
			return [
				{
					type: 'donation',
					amount: '$400.00',
					donor: {
						name: 'JK Rowling',
						id: 44,
					},
					status: 'completed',
					time: '2001-01-01',
					source: 'Save the Planet',
				},
				{
					type: 'donation',
					amount: '$400.00',
					donor: {
						name: 'JK Rowling',
						id: 44,
					},
					status: 'first_renewal',
					time: '2001-01-01',
					source: 'Save the Planet',
				},
				{
					type: 'donation',
					amount: '$400.00',
					donor: {
						name: 'JK Rowling',
						id: 44,
					},
					status: 'refunded',
					time: '2001-01-01',
					source: 'Save the Planet',
				},
				{
					type: 'donation',
					amount: '$400.00',
					donor: {
						name: 'JK Rowling',
						id: 44,
					},
					status: 'completed',
					time: '2001-01-01',
					source: 'Save the Planet',
				},
				{
					type: 'donation',
					amount: '$400.00',
					donor: {
						name: 'JK Rowling',
						id: 44,
					},
					status: 'renewal',
					time: '2001-01-01',
					source: 'Save the Planet',
				},
			];
		case 'top-donors':
			return [
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
				{
					type: 'donor',
					id: 44,
					name: 'JK Rowling',
					image: null,
					email: 'test@email.com',
					total: '$44,000',
					count: '48 Donations',
				},
			];
		default:
			return null;
	}
};
