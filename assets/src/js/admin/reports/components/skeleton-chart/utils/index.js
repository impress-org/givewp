export function getSkeletonData( type ) {
	switch ( type ) {
		case 'line': {
			const skeletonData = {
				datasets: [
					{
						data: [
							{
								y: 32,
								x: '01-01-2001',
							},
							{
								y: 41,
								x: '01-02-2001',
							},
							{
								y: 44,
								x: '01-03-2001',
							},
							{
								y: 12,
								x: '01-04-2001',
							},
							{
								y: 33,
								x: '01-05-2001',
							},
						],
						tooltips: [
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
						],
					},
				],
			};
			return skeletonData;
		}
		default: {
			const skeletonData = {
				labels: [
					'--',
					'--',
					'--',
					'--',
					'--',
				],
				datasets: [
					{
						label: '--',
						data: [
							32,
							41,
							37,
							12,
							32,
						],
						tooltips: [
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
							{
								title: '--',
								body: '--',
								footer: '',
							},
						],
					},
				],
			};
			return skeletonData;
		}
	}
}
