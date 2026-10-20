<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Donations\Storage\DonationStatusDecoder;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TestDonationStatusDecoder extends TestCase
{
    /**
     * @since TBD
     * @dataProvider cases
     */
    public function testDecode(string $postStatus, int $postParent, array $meta, array $expected)
    {
        $this->assertSame($expected, DonationStatusDecoder::decode($postStatus, $postParent, $meta));
    }

    /**
     * @since TBD
     */
    public function cases(): array
    {
        return [
            'single' => ['publish', 0, [], ['publish', 'single']],
            'legacy renewal status' => ['give_subscription', 12, [DonationMetaKeys::SUBSCRIPTION_ID => 3], ['publish', 'renewal']],
            'initial donation by flag' => ['publish', 0, [DonationMetaKeys::SUBSCRIPTION_ID => 3, DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION => '1'], ['publish', 'subscription']],
            'initial donation by parent' => ['pending', 0, [DonationMetaKeys::SUBSCRIPTION_ID => 3], ['pending', 'subscription']],
            'pending renewal' => ['pending', 12, [DonationMetaKeys::SUBSCRIPTION_ID => 3], ['pending', 'renewal']],
            'trashed keeps trash' => ['trash', 12, [DonationMetaKeys::SUBSCRIPTION_ID => 3], ['trash', 'renewal']],
        ];
    }
}
