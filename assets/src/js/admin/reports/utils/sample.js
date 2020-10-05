export const getSampleData = endpoint => {
	switch ( endpoint ) {
		case 'income':
			return {
				datasets: [
					{
						data: [
							{
								x: '2020-02-24 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 00:00:00',
								y: 223,
							},
							{
								x: '2020-02-26 00:00:00',
								y: 5,
							},
							{
								x: '2020-02-27 00:00:00',
								y: 376,
							},
							{
								x: '2020-02-28 00:00:00',
								y: 25,
							},
							{
								x: '2020-02-29 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 00:00:00',
								y: 250,
							},
							{
								x: '2020-03-02 00:00:00',
								y: 41,
							},
						],
						tooltips: [
							{
								title: '$0',
								body: '0 Donors',
								footer: 'Monday',
							},
							{
								title: '$223.00',
								body: '1 Donors',
								footer: 'Tuesday',
							},
							{
								title: '$5.00',
								body: '1 Donors',
								footer: 'Wednesday',
							},
							{
								title: '$376.00',
								body: '2 Donors',
								footer: 'Thursday',
							},
							{
								title: '$25.00',
								body: '1 Donors',
								footer: 'Friday',
							},
							{
								title: '$0',
								body: '0 Donors',
								footer: 'Saturday',
							},
							{
								title: '$250.00',
								body: '1 Donors',
								footer: 'Sunday',
							},
							{
								title: '$41.00',
								body: '1 Donors',
								footer: 'Monday',
							},
						],
					},
				],
			};
		case 'total-income':
			return {
				datasets: [
					{
						data: [
							{
								x: '2020-02-24 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 12:00:00',
								y: 223,
							},
							{
								x: '2020-02-25 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 12:00:00',
								y: 5,
							},
							{
								x: '2020-02-26 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 12:00:00',
								y: 250,
							},
							{
								x: '2020-02-27 15:00:00',
								y: 126,
							},
							{
								x: '2020-02-27 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 12:00:00',
								y: 25,
							},
							{
								x: '2020-02-28 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 12:00:00',
								y: 250,
							},
							{
								x: '2020-03-01 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 12:00:00',
								y: 41,
							},
							{
								x: '2020-03-02 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-03 00:00:00',
								y: 0,
							},
						],
						tooltips: [
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 9pm - Tue 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 12am - Tue 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 3am - Tue 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 6am - Tue 9am',
							},
							{
								title: '$223.00',
								body: 'Total Revenue',
								footer: 'Tue 9am - Tue 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 12pm - Tue 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 3pm - Tue 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 6pm - Tue 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Tue 9pm - Wed 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 12am - Wed 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 3am - Wed 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 6am - Wed 9am',
							},
							{
								title: '$5.00',
								body: 'Total Revenue',
								footer: 'Wed 9am - Wed 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 12pm - Wed 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 3pm - Wed 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 6pm - Wed 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Wed 9pm - Thu 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 12am - Thu 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 3am - Thu 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 6am - Thu 9am',
							},
							{
								title: '$250.00',
								body: 'Total Revenue',
								footer: 'Thu 9am - Thu 12pm',
							},
							{
								title: '$126.00',
								body: 'Total Revenue',
								footer: 'Thu 12pm - Thu 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 3pm - Thu 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 6pm - Thu 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Thu 9pm - Fri 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 12am - Fri 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 3am - Fri 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 6am - Fri 9am',
							},
							{
								title: '$25.00',
								body: 'Total Revenue',
								footer: 'Fri 9am - Fri 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 12pm - Fri 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 3pm - Fri 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 6pm - Fri 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Fri 9pm - Sat 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 12am - Sat 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 3am - Sat 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 6am - Sat 9am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 9am - Sat 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 12pm - Sat 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 3pm - Sat 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 6pm - Sat 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sat 9pm - Sun 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 12am - Sun 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 3am - Sun 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 6am - Sun 9am',
							},
							{
								title: '$250.00',
								body: 'Total Revenue',
								footer: 'Sun 9am - Sun 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 12pm - Sun 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 3pm - Sun 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 6pm - Sun 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '$41.00',
								body: 'Total Revenue',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '$0',
								body: 'Total Revenue',
								footer: 'Mon 9pm - Tue 12am',
							},
						],
						trend: 7.1,
						info: 'VS previous 7 days',
						highlight: '$920.00',
					},
				],
			};
		case 'total-donors':
			return {
				datasets: [
					{
						data: [
							{
								x: '2020-02-24 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 12:00:00',
								y: 1,
							},
							{
								x: '2020-02-25 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 12:00:00',
								y: 1,
							},
							{
								x: '2020-02-26 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 12:00:00',
								y: 1,
							},
							{
								x: '2020-02-27 15:00:00',
								y: 1,
							},
							{
								x: '2020-02-27 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 12:00:00',
								y: 1,
							},
							{
								x: '2020-02-28 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 12:00:00',
								y: 1,
							},
							{
								x: '2020-03-01 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 12:00:00',
								y: 1,
							},
							{
								x: '2020-03-02 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-03 00:00:00',
								y: 0,
							},
						],
						tooltips: [
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 9pm - Tue 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 12am - Tue 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 3am - Tue 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 6am - Tue 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Tue 9am - Tue 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 12pm - Tue 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 3pm - Tue 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 6pm - Tue 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Tue 9pm - Wed 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 12am - Wed 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 3am - Wed 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 6am - Wed 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Wed 9am - Wed 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 12pm - Wed 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 3pm - Wed 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 6pm - Wed 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Wed 9pm - Thu 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 12am - Thu 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 3am - Thu 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 6am - Thu 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Thu 9am - Thu 12pm',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Thu 12pm - Thu 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 3pm - Thu 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 6pm - Thu 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Thu 9pm - Fri 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 12am - Fri 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 3am - Fri 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 6am - Fri 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Fri 9am - Fri 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 12pm - Fri 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 3pm - Fri 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 6pm - Fri 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Fri 9pm - Sat 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 12am - Sat 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 3am - Sat 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 6am - Sat 9am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 9am - Sat 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 12pm - Sat 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 3pm - Sat 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 6pm - Sat 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sat 9pm - Sun 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 12am - Sun 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 3am - Sun 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 6am - Sun 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Sun 9am - Sun 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 12pm - Sun 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 3pm - Sun 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 6pm - Sun 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '1 Donors',
								body: 'Total Donors',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '0 Donors',
								body: 'Total Donors',
								footer: 'Mon 9pm - Tue 12am',
							},
						],
						trend: -12.5,
						info: 'VS previous 7 days',
						highlight: 7,
					},
				],
			};
		case 'average-donation':
			return {
				datasets: [
					{
						data: [
							{
								x: '2020-02-24 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 12:00:00',
								y: 223,
							},
							{
								x: '2020-02-25 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 12:00:00',
								y: 5,
							},
							{
								x: '2020-02-26 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 12:00:00',
								y: 250,
							},
							{
								x: '2020-02-27 15:00:00',
								y: 126,
							},
							{
								x: '2020-02-27 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 12:00:00',
								y: 25,
							},
							{
								x: '2020-02-28 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 12:00:00',
								y: 250,
							},
							{
								x: '2020-03-01 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 12:00:00',
								y: 41,
							},
							{
								x: '2020-03-02 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-03 00:00:00',
								y: 0,
							},
						],
						tooltips: [
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 23, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 24, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 24, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$223.00',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 25, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 25, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$5.00',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 26, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 26, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$250.00',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$126.00',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 27, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 27, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$25.00',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 28, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 28, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Feb 29, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Feb 29, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$250.00',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 1, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 1, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$41.00',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 2, 2020',
							},
							{
								title: '$0',
								body: 'Avg Donation',
								footer: 'Mar 2, 2020 - Mar 3, 2020',
							},
						],
						trend: 22.4,
						info: 'VS previous 7 days',
						highlight: '$131.43',
					},
				],
			};
		case 'total-refunds':
			return {
				datasets: [
					{
						data: [
							{
								x: '2020-02-24 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-24 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-25 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-26 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-27 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-28 21:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 00:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 03:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 06:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 09:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 12:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 15:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 18:00:00',
								y: 0,
							},
							{
								x: '2020-02-29 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 12:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-01 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 00:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 03:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 06:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 09:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 12:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 15:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 18:00:00',
								y: 0,
							},
							{
								x: '2020-03-02 21:00:00',
								y: 0,
							},
							{
								x: '2020-03-03 00:00:00',
								y: 0,
							},
						],
						tooltips: [
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 9pm - Tue 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 12am - Tue 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 3am - Tue 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 6am - Tue 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 9am - Tue 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 12pm - Tue 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 3pm - Tue 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 6pm - Tue 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Tue 9pm - Wed 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 12am - Wed 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 3am - Wed 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 6am - Wed 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 9am - Wed 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 12pm - Wed 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 3pm - Wed 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 6pm - Wed 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Wed 9pm - Thu 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 12am - Thu 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 3am - Thu 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 6am - Thu 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 9am - Thu 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 12pm - Thu 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 3pm - Thu 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 6pm - Thu 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Thu 9pm - Fri 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 12am - Fri 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 3am - Fri 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 6am - Fri 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 9am - Fri 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 12pm - Fri 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 3pm - Fri 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 6pm - Fri 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Fri 9pm - Sat 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 12am - Sat 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 3am - Sat 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 6am - Sat 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 9am - Sat 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 12pm - Sat 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 3pm - Sat 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 6pm - Sat 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sat 9pm - Sun 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 12am - Sun 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 3am - Sun 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 6am - Sun 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 9am - Sun 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 12pm - Sun 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 3pm - Sun 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 6pm - Sun 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Sun 9pm - Mon 12am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 12am - Mon 3am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 3am - Mon 6am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 6am - Mon 9am',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 9am - Mon 12pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 12pm - Mon 3pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 3pm - Mon 6pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 6pm - Mon 9pm',
							},
							{
								title: '0 Refunds',
								body: 'Total Refunds',
								footer: 'Mon 9pm - Tue 12am',
							},
						],
						trend: 0,
						info: 'VS previous 7 days',
						highlight: 0,
					},
				],
			};
		case 'payment-methods':
			return {
				labels: [
					'Stripe - Credit Card',
					'Offline Donation',
					'PayPal Standard',
					'Test Donation',
					'Stripe - Checkout',
				],
				datasets: [
					{
						data: [ 417, 255, 248, 0, 0 ],
						tooltips: [
							{
								title: '$417.00',
								body: '7 Payments',
								footer: 'Stripe - Credit Card',
							},
							{
								title: '$255.00',
								body: '7 Payments',
								footer: 'Offline Donation',
							},
							{
								title: '$248.00',
								body: '7 Payments',
								footer: 'PayPal Standard',
							},
							{
								title: '$0',
								body: '7 Payments',
								footer: 'Test Donation',
							},
							{
								title: '$0',
								body: '7 Payments',
								footer: 'Stripe - Checkout',
							},
						],
					},
				],
			};
		case 'payment-statuses':
			return {
				labels: [ 'Completed', 'Pending', 'Refunded', 'Abandoned' ],
				datasets: [
					{
						data: [ 7, 0, 0, 0 ],
						tooltips: [
							{
								title: '7 Payments',
								body: 'Completed',
								footer: '',
							},
							{
								title: '0 Payments',
								body: 'Pending',
								footer: '',
							},
							{
								title: '0 Payments',
								body: 'Refunded',
								footer: '',
							},
							{
								title: '0 Payments',
								body: 'Abandoned',
								footer: '',
							},
						],
					},
				],
			};
		case 'form-performance':
			return {
				forms: null,
				datasets: [
					{
						data: [ 500, 379, 41 ],
						tooltips: [
							{
								title: '$500.00',
								body: '2 Donations',
								footer: 'Save the Rainforest',
							},
							{
								title: '$379.00',
								body: '4 Donations',
								footer: 'Homeless Outreach Fund',
							},
							{
								title: '$41.00',
								body: '1 Donations',
								footer: 'Support the Veterans Choir',
							},
						],
						labels: [ 'Save the Rainforest', 'Homeless Outreach Fund', 'Support the Veterans Choir' ],
					},
				],
			};
		case 'recent-donations':
			return [
				{
					type: 'donation',
					donation: {
						ID: 2189,
					},
					status: 'completed',
					amount: '$41.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2189',
					time: '2020-03-02 11:21:00',
					donor: {
						name: 'Philippine Ferraraccio',
						id: '2096',
					},
					source: 'Support the Veterans Choir',
				},
				{
					type: 'donation',
					donation: {
						ID: 2188,
					},
					status: 'completed',
					amount: '$250.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2188',
					time: '2020-03-01 11:21:00',
					donor: {
						name: 'Sheridan Frowd',
						id: '2095',
					},
					source: 'Save the Rainforest',
				},
				{
					type: 'donation',
					donation: {
						ID: 2187,
					},
					status: 'completed',
					amount: '$25.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2187',
					time: '2020-02-28 11:21:00',
					donor: {
						name: 'Loraine Swettenham',
						id: '2094',
					},
					source: 'Homeless Outreach Fund',
				},
				{
					type: 'donation',
					donation: {
						ID: 2186,
					},
					status: 'completed',
					amount: '$126.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2186',
					time: '2020-02-27 12:21:00',
					donor: {
						name: 'Les Leddie',
						id: '2082',
					},
					source: 'Homeless Outreach Fund',
				},
				{
					type: 'donation',
					donation: {
						ID: 2185,
					},
					status: 'completed',
					amount: '$250.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2185',
					time: '2020-02-27 11:21:00',
					donor: {
						name: 'Flint Cruikshanks',
						id: '2093',
					},
					source: 'Save the Rainforest',
				},
				{
					type: 'donation',
					donation: {
						ID: 2184,
					},
					status: 'completed',
					amount: '$5.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2184',
					time: '2020-02-26 11:21:00',
					donor: {
						name: 'Dunn Layman',
						id: '2092',
					},
					source: 'Homeless Outreach Fund',
				},
				{
					type: 'donation',
					donation: {
						ID: 2183,
					},
					status: 'completed',
					amount: '$223.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2183',
					time: '2020-02-25 11:21:00',
					donor: {
						name: 'Barrie Hartop',
						id: '2091',
					},
					source: 'Homeless Outreach Fund',
				},
				{
					type: 'donation',
					donation: {
						ID: 2182,
					},
					status: 'cancelled',
					amount: '$25.00',
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=2182',
					time: '2020-02-24 11:21:00',
					donor: {
						name: 'Teressa Corrado',
						id: '2090',
					},
					source: 'Help Feed America',
				},
			];
		case 'top-donors':
			return [
				{
					type: 'donor',
					earnings: 250,
					total: '$250.00',
					donations: 1,
					count: '1 Donation',
					name: 'Sheridan Frowd',
					email: 'sfrowd86@usgs.gov',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1795',
				},
				{
					type: 'donor',
					earnings: 250,
					total: '$250.00',
					donations: 1,
					count: '1 Donation',
					name: 'Flint Cruikshanks',
					email: 'fcruikshanks84@unesco.org',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1793',
				},
				{
					type: 'donor',
					earnings: 223,
					total: '$223.00',
					donations: 1,
					count: '1 Donation',
					name: 'Barrie Hartop',
					email: 'bhartop82@goo.ne.jp',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1791',
				},
				{
					type: 'donor',
					earnings: 126,
					total: '$126.00',
					donations: 1,
					count: '1 Donation',
					name: 'Les Leddie',
					email: 'lleddie7t@zdnet.com',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1782',
				},
				{
					type: 'donor',
					earnings: 41,
					total: '$41.00',
					donations: 1,
					count: '1 Donation',
					name: 'Philippine Ferraraccio',
					email: 'pferraraccio87@dailymail.co.uk',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1796',
				},
				{
					type: 'donor',
					earnings: 25,
					total: '$25.00',
					donations: 1,
					count: '1 Donation',
					name: 'Loraine Swettenham',
					email: 'lswettenham85@opera.com',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1794',
				},
				{
					type: 'donor',
					earnings: 5,
					total: '$5.00',
					donations: 1,
					count: '1 Donation',
					name: 'Dunn Layman',
					email: 'dlayman83@phoca.cz',
					image: null,
					url:
						'https://give.local/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=1792',
				},
			];
		case 'income-breakdown':
			return [
				{
					Date: 'February 24, 2020',
					Donors: 0,
					Donations: '$0',
					Refunds: 0,
					Net: '$0',
				},
				{
					Date: 'February 25, 2020',
					Donors: 0,
					Donations: '$0',
					Refunds: 0,
					Net: '$0',
				},
				{
					Date: 'February 26, 2020',
					Donors: 1,
					Donations: '$223.00',
					Refunds: 0,
					Net: '$223.00',
				},
				{
					Date: 'February 27, 2020',
					Donors: 1,
					Donations: '$5.00',
					Refunds: 0,
					Net: '$5.00',
				},
				{
					Date: 'February 28, 2020',
					Donors: 2,
					Donations: '$376.00',
					Refunds: 0,
					Net: '$376.00',
				},
				{
					Date: 'February 29, 2020',
					Donors: 1,
					Donations: '$25.00',
					Refunds: 0,
					Net: '$25.00',
				},
				{
					Date: 'March 1, 2020',
					Donors: 0,
					Donations: '$0',
					Refunds: 0,
					Net: '$0',
				},
				{
					Date: 'March 2, 2020',
					Donors: 1,
					Donations: '$250.00',
					Refunds: 0,
					Net: '$250.00',
				},
				{
					Date: 'March 3, 2020',
					Donors: 1,
					Donations: '$41.00',
					Refunds: 0,
					Net: '$41.00',
				},
			];
		default:
			return null;
	}
};
