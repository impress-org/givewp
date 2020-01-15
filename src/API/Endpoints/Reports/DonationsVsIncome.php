<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class DonationsVsIncome extends Endpoint {

	public function __construct() {
		$this->endpoint = 'donations-vs-income';
	}

	public function get_report($request) {

		$start = date_create($request['start']);
		$end = date_create($request['end']);
		$diff = date_diff($start, $end);

		$data = [];

		switch(true) {
			case ($diff->days > 400):
				$data = $this->get_data($start, $end, 'P1Y');
				break;
			case ($diff->days > 120):
				$data = $this->get_data($start, $end, 'P1M');
				break;
			case ($diff->days > 30):
				$data = $this->get_data($start, $end, 'P7D');
				break;
			case ($diff->days > 10):
				$data = $this->get_data($start, $end, 'P3D');
				break;
			case ($diff->days > 4):
				$data = $this->get_data($start, $end, 'P1D');
				break;
			case ($diff->days > 1):
				$data = $this->get_data($start, $end, 'PT6H');
				break;
			case ($diff->days >= 0):
				$data = $this->get_data($start, $end, 'PT1H');
				break;
		}

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => $data
		]);
	}

	public function get_data($start, $end, $interval) {

		$stats = new \Give_Payment_Stats();

		$labels = [];
		$donations = [];
		$income = [];
		$periods = [];

		$dateInterval = new \DateInterval($interval);
		while ( $start < $end ) {

			$periodStart = $start->format('Y-m-d H:i:s');

			// Add interval to get period end
			$periodEnd = clone $start;
			date_add($periodEnd, $dateInterval);

			$periodEnd = $periodEnd->format('Y-m-d H:i:s');

			$donationsForPeriod = $stats->get_sales( 0, $periodStart, $periodEnd );
			$incomeForPeriod = $stats->get_earnings( 0, $periodStart, $periodEnd );

			array_push($donations, $donationsForPeriod);
			array_push($income, $incomeForPeriod);
			array_push($labels, $periodEnd);
			array_push($periods, $periodStart);

			date_add($start, $dateInterval);
		}

		$data = [
			'periods' => $periods,
			'labels' => $labels,
			'datasets' => [
				[
					'label' => 'Donations',
					'data' => $donations
				],
				[
					'label' => 'Income',
					'data' => $income
				]
			]
		];

		return $data;

	}
}
