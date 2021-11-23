<?php

namespace Give\TestData\Addons\RecurringDonations;

use DateInterval;
use DateTime;
use Exception;
use Give\TestData\Framework\Factory;

/**
 * Class RecurringDonationFactory
 * @package Give\TestData\RecurringDonations
 */
class RecurringDonationFactory extends Factory
{

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $parentId;

    /**
     * @var int
     */
    private $productId;

    /**
     * @param int $id
     */
    public function setCustomerId($id)
    {
        $this->customerId = $id;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        if (is_null($this->customerId) || ! $this->customerId) {
            return $this->randomDonor();
        }

        return $this->customerId;
    }

    /**
     * @return string[]
     */
    public function getPeriods()
    {
        return [
            'day' => 'P1D',
            'week' => 'P1W',
            'month' => 'P1M',
            'quarter' => 'P3M',
            'year' => 'P1Y',
        ];
    }

    /**
     * @return string
     */
    public function getRandomPeriod()
    {
        return $this->faker->randomElement(array_keys($this->getPeriods()));
    }

    /**
     * @param string $period
     *
     * @return string
     */
    public function getIntervalForPeriod($period)
    {
        $periods = $this->getPeriods();

        return $periods[$period];
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        if (is_null($this->amount) || ! $this->amount) {
            return $this->randomAmount();
        }

        return $this->amount;
    }

    /**
     * @param int $id
     */
    public function setParentDonationId($id)
    {
        $this->parentId = $id;
    }

    /**
     * @return int
     */
    public function getParentDonationId()
    {
        if (is_null($this->parentId) || ! $this->parentId) {
            return $this->randomDonation();
        }

        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getRandomStatus()
    {
        // Remove expired
        $statuses = array_filter(
            give_recurring_get_subscription_statuses_key(),
            function ($status) {
                return ('expired' !== $status);
            }
        );

        return $this->faker->randomElement($statuses);
    }

    /**
     * @param int $id
     */
    public function setProductId($id)
    {
        $this->productId = $id;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        if (is_null($this->productId) || ! $this->productId) {
            return $this->randomDonationForm();
        }

        return $this->productId;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function definition()
    {
        $amount = $this->getAmount();
        $period = $this->getRandomPeriod();
        $date_created = $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s');
        $date_expire = new DateTime($date_created);

        // Set the correct expiration date
        $date_expire->add(new DateInterval($this->getIntervalForPeriod($period)));

        return [
            'customer_id' => $this->getCustomerId(),
            'period' => $period,
            'frequency' => 1,
            'initial_amount' => $amount,
            'recurring_amount' => $amount,
            'parent_payment_id' => $this->getParentDonationId(),
            'product_id' => $this->getProductId(),
            'created' => $date_created,
            'expiration' => $date_expire->format('Y-m-d H:i:s'),
            'status' => $this->getRandomStatus(),
        ];
    }
}
