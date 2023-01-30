<?php

namespace Give\NextGen\DonationForm\Models;

use DateTime;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\NextGen\DonationForm\Actions\ConvertQueryDataToDonationForm;
use Give\NextGen\DonationForm\Factories\DonationFormFactory;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\Framework\Blocks\BlockCollection;

/**
 * @since 0.1.0
 *
 * @property int $id
 * @property string $title
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property DonationFormStatus $status
 * @property FormSettings $settings
 * @property BlockCollection $blocks
 */
class DonationForm extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'title' => 'string',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => DonationFormStatus::class,
        'settings' => FormSettings::class,
        'blocks' => BlockCollection::class,
    ];

    /**
     * @since 0.1.0
     * @return DonationFormFactory
     */
    public static function factory(): DonationFormFactory
    {
        return new DonationFormFactory(static::class);
    }

    /**
     * Find donation form by ID
     *
     * @since 0.1.0
     *
     * @param  int  $id
     *
     * @return DonationForm|null
     */
    public static function find($id)
    {
        return give(DonationFormRepository::class)->getById($id);
    }

    /**
     * @since 0.1.0
     *
     * @param  array  $attributes
     *
     * @return DonationForm
     * @throws Exception
     */
    public static function create(array $attributes): DonationForm
    {
        $donationForm = new static($attributes);

        give(DonationFormRepository::class)->insert($donationForm);

        return $donationForm;
    }

    /**
     * @since 0.1.0
     *
     * @return void
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            give(DonationFormRepository::class)->insert($this);
        } else {
            give(DonationFormRepository::class)->update($this);
        }
    }

    /**
     * @since 0.1.0
     *
     * @throws Exception
     */
    public function delete()
    {
        give(DonationFormRepository::class)->delete($this);
    }

    /**
     * @since 0.1.0
     *
     * @return ModelQueryBuilder<DonationForm>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(DonationFormRepository::class)->prepareQuery();
    }

    /**
     * @since 0.1.0
     *
     * @param  object  $object
     */
    public static function fromQueryBuilderObject($object): DonationForm
    {
        return (new ConvertQueryDataToDonationForm())($object);
    }

    /**
     *
     * @since 0.1.0
     */
    public function schema(): Form
    {
        return give(DonationFormRepository::class)->getFormSchemaFromBlocks($this->id, $this->blocks);
    }
}
