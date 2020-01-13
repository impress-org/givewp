<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class FormPerformance extends Endpoint {

	public function __construct() {
		$this->endpoint = 'form-performance';
	}

	public function get_report($request) {

		$labels = [];
		$data = [];

		$stats = new \Give_Payment_Stats;
		$topForms = $stats->get_best_selling(5);

		foreach ($topForms as $form) {
			$title = get_the_title($form->form_id);
			array_push($labels, $title);
			array_push($data, $form->sales);
		}

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				'top' => $topForms,
				'labels' => $labels,
				'datasets' => [
					[
						'label' => 'Sales',
						'data' => $data
					]
				],
			]
		]);
	}
}
