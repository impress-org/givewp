<?php

namespace Give\Donations\Models;

use DateTime;
use Give\Donations\Factories\DonationNoteFactory;
use Give\Donations\ValueObjects\DonationNoteType;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 2.21.0
 *
 * @property int $id
 * @property int $donationId
 * @property string $content
 * @property DonationNoteType $type
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
        'type' => DonationNoteType::class,
        'createdAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donation' => Relationship::BELONGS_TO,
    ];

    /**
     * @since 2.21.0
     *
     * @return DonationNote|null
     */
    public static function find($id)
    {
        return give()->donations->notes->getById($id);
    }


    /**
     * @since 2.21.0
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
     * @since 2.21.0
     *
     * @return void
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
     * @since 2.21.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give()->donations->notes->delete($this);
    }

    /**
     * @since 2.21.0
     *
     * @return ModelQueryBuilder<DonationNote>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->donations->notes->prepareQuery();
    }

    /**
     * @since 2.21.0
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function donation(): ModelQueryBuilder
    {
        return give()->donations->queryById($this->donationId);
    }

    /**
     * @since 2.21.0
     *
     * @param  object  $object
     */
    public static function fromQueryBuilderObject($object): DonationNote
    {
        return new DonationNote([
            'id' => (int)$object->id,
            'type' => $object->type ? new DonationNoteType($object->type) : DonationNoteType::ADMIN(),
            'donationId' => (int)$object->donationId,
            'content' => (string)$object->content,
            'createdAt' => Temporal::toDateTime($object->createdAt),
        ]);
    }

    /**
     * @since 2.21.0
     */
    public static function factory(): DonationNoteFactory
    {
        return new DonationNoteFactory(static::class);
    }
}
