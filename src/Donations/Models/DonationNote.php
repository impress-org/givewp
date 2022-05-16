<?php

namespace Give\Donations\Models;

use DateTime;
use Give\Donations\Factories\DonationNoteFactory;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 *
 * @property int $id
 * @property int $donationId
 * @property string $content
 * @property DateTime $createdAt
 * @property Donation $donation
 */
class DonationNote extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'donationId' => 'int',
        'content' => 'string',
        'createdAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donation' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     *
     * @param  int  $id
     *
     * @return DonationNote
     */
    public static function find($id): DonationNote
    {
        return give()->donations->notes->getById($id);
    }


    /**
     * @unreleased
     *
     * @param  array  $attributes
     *
     * @return $this
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): DonationNote
    {
        $donationNote = new static($attributes);

        give()->donations->notes->insert($donationNote);

        return $donationNote;
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            give()->donations->notes->insert($this);
        } else{
            give()->donations->notes->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @return bool
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete()
    {
        return give()->donations->notes->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder
     */
    public static function query()
    {
        return give()->donations->notes->prepareQuery();
    }

    /**
     * @return ModelQueryBuilder
     */
    public function donation()
    {
        return give()->donations->queryById($this->donationId);
    }

    /**
     * @unreleased
     *
     * @param  object  $object
     * @return DonationNote
     */
    public static function fromQueryBuilderObject($object)
    {
        return new DonationNote([
            'id' => (int)$object->id,
            'donationId' => (int)$object->donationId,
            'createdAt' => Temporal::toDateTime($object->createdAt),
            'content' => (string)$object->content,
        ]);
    }

    /**
     * @return DonationNoteFactory
     */
    public static function factory()
    {
        return new DonationNoteFactory(static::class);
    }
}
