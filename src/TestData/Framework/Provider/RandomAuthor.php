<?php

namespace Give\TestData\Framework\Provider;

/**
 * Returns a random Author ID from the users table.
 */
class RandomAuthor extends RandomProvider {

	public function __invoke() {
		$authorIDs = get_users(
			[
				'fields'   => 'ID',
				'role__in' => [ 'administrator', 'editor', 'author' ],
			]
		);

		return $this->faker->randomElement( $authorIDs );
	}
}
