<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donors\Models\DonorNote;
use Give\Donors\ValueObjects\DonorNoteMetaKeys;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @since 4.4.0
 */
class DonorNotesRepository
{
    /**
     * @since 4.4.0
     *
     * @var string[]
     */
    private $requiredDonorProperties = [
        'donorId',
        'content',
    ];

    /**
     * @since 4.4.0
     */
    public function getById(int $noteId): ?DonorNote
    {
        return $this->prepareQuery()
            ->where('comment_ID', $noteId)
            ->get();
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function insert(DonorNote $donorNote)
    {
        if ( ! $donorNote->type) {
            $donorNote->type = DonorNoteType::ADMIN();
        }

        $this->validateDonorNote($donorNote);

        Hooks::doAction('givewp_donor_note_creating', $donorNote);

        $dateCreated = Temporal::withoutMicroseconds($donorNote->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);


        DB::beginTransaction();

        try {
            DB::table('give_comments')
                ->insert([
                    'comment_content' => $donorNote->content,
                    'comment_date' => $dateCreatedFormatted,
                    'comment_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'comment_parent' => $donorNote->donorId,
                    'comment_type' => 'donor',
                ]);

            $commentId = DB::last_insert_id();

            if ($donorNote->type->isDonor()) {
                DB::table('give_commentmeta')
                    ->insert([
                        'give_comment_id' => $commentId,
                        'meta_key' => DonorNoteMetaKeys::TYPE,
                        'meta_value' => DonorNoteType::DONOR,
                    ]);
            }
        } catch (Exception $exception) {
            DB::rollback();

            Log::error('Failed creating a donor note', compact('donorNote'));

            throw new $exception('Failed creating a donor note');
        }

        DB::commit();

        $donorNote->id = $commentId;
        $donorNote->createdAt = $dateCreated;

        Hooks::doAction('givewp_donor_note_created', $donorNote);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function update(DonorNote $donorNote)
    {
        $this->validateDonorNote($donorNote);

        Hooks::doAction('givewp_donor_note_updating', $donorNote);

        DB::beginTransaction();

        try {
            DB::table('give_comments')
                ->where('comment_ID', $donorNote->id)
                ->update([
                    'comment_content' => $donorNote->content,
                    'comment_parent' => $donorNote->donorId,
                    'comment_type' => 'donor',
                ]);
            if ($donorNote->isDirty('type') && $donorNote->type->isDonor()) {
                $this->upsertDonorNoteType($donorNote);
            }
        } catch (Exception $exception) {
            DB::rollback();

            Log::error('Failed updating a donor note', compact('donorNote'));

            throw new $exception('Failed updating a donor note');
        }

        DB::commit();

        Hooks::doAction('givewp_donor_note_updated', $donorNote);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function delete(DonorNote $donorNote): bool
    {
        DB::beginTransaction();

        Hooks::doAction('givewp_donor_note_deleting', $donorNote);

        try {
            DB::table('give_comments')
                ->where('comment_ID', $donorNote->id)
                ->delete();

            DB::table('give_commentmeta')
                ->where('give_comment_id', $donorNote->id)
                ->delete();
        } catch (Exception $exception) {
            DB::rollback();

            Log::error('Failed deleting a donor note', compact('donorNote'));

            throw new $exception('Failed deleting a donor note');
        }

        DB::commit();

        Hooks::doAction('givewp_donor_note_deleted', $donorNote);

        return true;
    }

    /**
     * @since 4.4.0
     */
    public function queryByDonorId(int $donorId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('comment_parent', $donorId)
            ->orderBy('comment_ID', 'DESC');
    }

    /**
     * @since 4.4.0
     *
     * @return void
     */
    private function validateDonorNote(DonorNote $donorNote)
    {
        foreach ($this->requiredDonorProperties as $key) {
            if ( ! isset($donorNote->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }

        if ( ! $donorNote->donor) {
            throw new InvalidArgumentException('Invalid donorId, Donor does not exist');
        }
    }

    /**
     * @since 4.4.0
     *
     * @return ModelQueryBuilder<DonorNote>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(DonorNote::class);

        return $builder->from('give_comments')
            ->select(
                ['comment_ID', 'id'],
                ['comment_parent', 'donorId'],
                ['comment_content', 'content'],
                ['comment_date', 'createdAt']
            )
            ->attachMeta(
                'give_commentmeta',
                'comment_ID',
                'give_comment_id',
                ...DonorNoteMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('comment_type', 'donor');
    }

    /**
     * @since 4.4.0
     */
    private function upsertDonorNoteType(DonorNote $donorNote)
    {
        $table = DB::table('give_commentmeta');

        $query = $table
            ->where('give_comment_id', $donorNote->id)
            ->where('meta_key', DonorNoteMetaKeys::TYPE)
            ->get();

        if ( ! $query) {
            $table->insert([
                'give_comment_id' => $donorNote->id,
                'meta_key' => DonorNoteMetaKeys::TYPE,
                'meta_value' => $donorNote->type->getValue(),
            ]);
        } else {
            $table
                ->where('give_comment_id', $donorNote->id)
                ->where('meta_key', DonorNoteMetaKeys::TYPE)
                ->update([
                    'meta_value' => $donorNote->type->getValue(),
                ]);
        }
    }
}
