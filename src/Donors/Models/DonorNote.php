<?php

namespace Give\Donors\Models;

use DateTime;
use Exception;
use Give\Donors\Factories\DonorNoteFactory;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 *
 * @property int           $id
 * @property int           $donorId
 * @property string        $content
 * @property DonorNoteType $type
 * @property DateTime      $createdAt
 * @property Donor         $donor
 */
class DonorNote extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'donorId' => 'int',
        'content' => 'string',
        'type' => DonorNoteType::class,
        'createdAt' => DateTime::class,
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donor' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     */
    public static function find($id): ?DonorNote
    {
        return give()->donors->notes->getById($id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public static function create(array $attributes): DonorNote
    {
        $donorNote = new static($attributes);

        give()->donors->notes->insert($donorNote);

        return $donorNote;
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function save()
    {
        if ( ! $this->id) {
            give()->donors->notes->insert($this);
        } else {
            give()->donors->notes->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete(): bool
    {
        return give()->donors->notes->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<DonorNote>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->donors->notes->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Donor>
     */
    public function donor(): ModelQueryBuilder
    {
        return give()->donors->queryById($this->donorId);
    }

    /**
     * @unreleased
     */
    public static function fromQueryBuilderObject($object): DonorNote
    {
        return new DonorNote([
            'id' => (int)$object->id,
            'type' => $object->type ? new DonorNoteType($object->type) : DonorNoteType::ADMIN(),
            'donorId' => (int)$object->donorId,
            'content' => (string)$object->content,
            'createdAt' => Temporal::toDateTime($object->createdAt),
        ]);
    }

    /**
     * @unreleased
     */
    public static function factory(): DonorNoteFactory
    {
        return new DonorNoteFactory(static::class);
    }
}
