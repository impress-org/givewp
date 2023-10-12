<?php

namespace Give\DonationForms\Models;

use DateTime;
use Give\DonationForms\Actions\ConvertQueryDataToDonationForm;
use Give\DonationForms\Factories\DonationFormFactory;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\DonationForm as Form;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;

/**
 * @since 3.0.0
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
     * @since 3.0.0
     * @return DonationFormFactory
     */
    public static function factory(): DonationFormFactory
    {
        return new DonationFormFactory(static::class);
    }

    /**
     * Find donation form by ID
     *
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function delete(): bool
    {
        return give(DonationFormRepository::class)->delete($this);
    }

    /**
     * @since 3.0.0
     *
     * @return ModelQueryBuilder<DonationForm>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(DonationFormRepository::class)->prepareQuery();
    }

    /**
     * @since 3.0.0
     *
     * @param  object  $object
     */
    public static function fromQueryBuilderObject($object): DonationForm
    {
        return (new ConvertQueryDataToDonationForm())($object);
    }

    /**
     *
     * @since 3.0.0
     * @throws NameCollisionException
     */
    public function schema(): Form
    {
        return give(DonationFormRepository::class)->getFormSchemaFromBlocks($this->id, $this->blocks);
    }
}
