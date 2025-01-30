<?php

namespace Give\Campaigns\Models;

use DateTime;
use Exception;
use Give\Campaigns\Actions\ConvertQueryDataToCampaign;
use Give\Campaigns\DataTransferObjects\CampaignGoalData;
use Give\Campaigns\Factories\CampaignFactory;
use Give\Campaigns\Repositories\CampaignPageRepository;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Repositories\DonationFormsRepository;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\JoinQueryBuilder;

/**
 * @unreleased
 *
 * @property int              $id
 * @property int              $defaultFormId
 * @property CampaignType     $type
 * @property bool $enableCampaignPage
 * @property string           $title
 * @property string           $url
 * @property string           $shortDescription
 * @property string           $longDescription
 * @property string           $logo
 * @property string           $image
 * @property string           $primaryColor
 * @property string           $secondaryColor
 * @property int              $goal
 * @property CampaignGoalType $goalType
 * @property CampaignStatus   $status
 * @property DateTime         $startDate
 * @property DateTime         $endDate
 * @property DateTime         $createdAt
 */
class Campaign extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'defaultFormId' => 'int',
        'type' => CampaignType::class,
        'enableCampaignPage' => ['bool', true],
        'title' => 'string',
        'shortDescription' => 'string',
        'longDescription' => 'string',
        'logo' => 'string',
        'image' => 'string',
        'primaryColor' => 'string',
        'secondaryColor' => 'string',
        'goal' => 'int',
        'goalType' => CampaignGoalType::class,
        'status' => CampaignStatus::class,
        'startDate' => DateTime::class,
        'endDate' => DateTime::class,
        'createdAt' => DateTime::class,
    ];

    /**
     * @unreleased
     */
    public function defaultForm(): ?DonationForm
    {
        return give(DonationFormsRepository::class)->getById($this->defaultFormId);
    }

    /**
     * @unreleased
     */
    public function forms(): ModelQueryBuilder
    {
        return DonationForm::query()
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_campaign_forms', 'campaign_forms')
                    ->on('campaign_forms.form_id', 'id');
            })
            ->where('campaign_forms.campaign_id', $this->id);
    }

    /**
     * @unreleased
     */
    public function page()
    {
        return give(CampaignPageRepository::class)->findByCampaignId($this->id);
    }

    /**
     * @unreleased
     */
    public static function factory(): CampaignFactory
    {
        return new CampaignFactory(static::class);
    }

    /**
     * Find campaign by ID
     *
     * @unreleased
     */
    public static function find($id): ?Campaign
    {
        return give(CampaignRepository::class)->getById($id);
    }

    /**
     * Find campaign by Form ID
     *
     * @unreleased
     */
    public static function findByFormId(int $formId): ?Campaign
    {
        return give(CampaignRepository::class)->getByFormId($formId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public static function create(array $attributes): Campaign
    {
        $campaign = new static($attributes);

        give(CampaignRepository::class)->insert($campaign);

        return $campaign;
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save(): void
    {
        if ( ! $this->id) {
            give(CampaignRepository::class)->insert($this);
        } else {
            give(CampaignRepository::class)->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete(): bool
    {
        return give(CampaignRepository::class)->delete($this);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function merge(Campaign ...$campaignsToMerge): bool
    {
        return give(CampaignRepository::class)->mergeCampaigns($this, ...$campaignsToMerge);
    }

    public function getGoalStats(): array
    {
        return (new CampaignGoalData($this))->toArray();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Campaign>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(CampaignRepository::class)->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): Campaign
    {
        return (new ConvertQueryDataToCampaign())($object);
    }
}
