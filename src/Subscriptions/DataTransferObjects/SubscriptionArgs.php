<?php

namespace Give\Subscriptions\DataTransferObjects;

/**
 * Class SubscriptionArgs
 * @since 2.18.0
 */
final class SubscriptionArgs
{
    /**
     * @var string
     */
    public $period;
    /**
     * @var string
     */
    public $times;
    /**
     * @var string
     */
    public $frequency;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var string
     */
    public $formId;
    /**
     * @var string
     */
    public $priceId;
    /**
     * @var string
     */
    public $price;
    /**
     * @var string
     */
    public $status;
    /**
     * @var float|int|string
     */
    public $initialAmount;
    /**
     * @var float|int|string
     */
    public $recurringAmount;
    /**
     * @var string
     */
    public $periodInterval;
    /**
     * @var float|int
     */
    public $frequencyIntervalCount;
    /**
     * @var int
     */
    public $billTimes;
    /**
     * @var int|mixed
     */
    public $recurringFeeAmount;
    /**
     * @var mixed|string
     */
    public $profileId;
    /**
     * @var mixed|string
     */
    public $transactionId;

    /**
     * Convert data from request into DTO
     *
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->period = $request['period'];
        $self->times = $request['times'];
        $self->frequency = $request['frequency'];
        $self->formTitle = $request['formTitle'];
        $self->formId = $request['formId'];
        $self->priceId = $request['priceId'];
        $self->price = $request['price'];
        $self->status = $request['status'];
        $self->initialAmount = give_sanitize_amount_for_db($self->price);
        $self->recurringAmount = give_sanitize_amount_for_db($self->price);
        $self->recurringFeeAmount = isset($request['recurringFeeAmount']) ? $request['recurringFeeAmount'] : 0;
        $self->profileId = isset($request['profileId']) ? $request['profileId'] : '';
        $self->transactionId = isset($request['transactionId']) ? $request['transactionId'] : '';
        $self->periodInterval = self::get_interval($self->period, $self->frequency);
        $self->frequencyIntervalCount = self::get_interval_count($self->period, $self->frequency);

        // @phpstan-ignore-next-line function is undefined in add-on. Also, the calling class, CreateSubscriptionAction, does not seem to be used...
        $self->billTimes = give_recurring_calculate_times($self->times, $self->frequency);

        return $self;
    }

    /**
     * @since 2.18.0
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->formTitle,
            'id' => $this->formId,
            // @TODO Deprecate w/ backwards compatiblity.
            'form_id' => $this->formId,
            'price_id' => $this->priceId,
            'initial_amount' => $this->initialAmount,
            // add fee here in future.
            'recurring_amount' => $this->recurringAmount,
            'period' => $this->periodInterval,
            'frequency' => $this->frequencyIntervalCount,
            // Passed interval. Example: charge every 3 weeks.
            'bill_times' => $this->billTimes,
            'profile_id' => '',
            // Profile ID for this subscription - This is set by the payment gateway.
            'transaction_id' => '',
            // Transaction ID for this subscription - This is set by the payment gateway.
            'status' => 'pending',
        ];
    }

    /**
     * Gets interval length and interval unit for Authorize.net based on Give subscription period.
     *
     * @since 2.18.0
     *
     * @param int    $frequency
     *
     * @param string $period
     *
     * @return string
     */
    private static function get_interval($period, $frequency)
    {
        $interval = $period;

        if ($period === 'quarter') {
            $interval = 'month';
        }

        return $interval;
    }

    /**
     * Gets interval length and interval unit for Authorize.net based on Give subscription period.
     *
     * @since 2.18.0
     *
     * @param int    $frequency
     *
     * @param string $period
     *
     * @return float|int
     */
    private static function get_interval_count($period, $frequency)
    {
        $interval_count = $frequency;

        if ($period === 'quarter') {
            $interval_count = 3 * $frequency;
        }

        return $interval_count;
    }
}
