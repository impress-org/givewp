<?php

namespace Give\Donations\Repositories;

use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationNoteMetaKeys;
use Give\Donations\ValueObjects\DonationNoteType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @since 2.21.0
 */
class DonationNotesRepository
{

    /**
     * @since 2.21.0
     *
     * @var string[]
     */
    private $requiredDonationProperties = [
        'donationId',
        'content',
    ];

    /**
     * @since 2.21.0
     *
     * @param int $noteId
     *
     * @return DonationNote|null
     */
    public function getById(int $noteId)
    {
        return $this->prepareQuery()
            ->where('comment_ID', $noteId)
            ->get();
    }

    /**
     * @since 2.21.0
     *
     * @param DonationNote $donationNote
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(DonationNote $donationNote)
    {
        if (!$donationNote->type) {
            $donationNote->type = DonationNoteType::ADMIN();
        }

        $this->validateDonationNote($donationNote);

        Hooks::doAction('givewp_donation_note_creating', $donationNote);

        $dateCreated = Temporal::withoutMicroseconds($donationNote->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);


        DB::query('START TRANSACTION');

        try {
            DB::table('give_comments')
                ->insert([
                    'comment_content' => $donationNote->content,
                    'comment_date' => $dateCreatedFormatted,
                    'comment_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'comment_parent' => $donationNote->donationId,
                    'comment_type' => 'donation',
                ]);

            $commentId = DB::last_insert_id();

            if ($donationNote->type->isDonor()) {
                DB::table('give_commentmeta')
                    ->insert([
                        'give_comment_id' => $commentId,
                        'meta_key' => DonationNoteMetaKeys::TYPE,
                        'meta_value' => DonationNoteType::DONOR,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation note', compact('donationNote'));

            throw new $exception('Failed creating a donation note');
        }

        DB::query('COMMIT');

        $donationNote->id = $commentId;
        $donationNote->createdAt = $dateCreated;

        Hooks::doAction('givewp_donation_note_created', $donationNote);
    }

    /**
     * @since 2.21.0
     *
     * @param DonationNote $donationNote
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(DonationNote $donationNote)
    {
        $this->validateDonationNote($donationNote);

        Hooks::doAction('givewp_donation_note_updating', $donationNote);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_comments')
                ->where('comment_ID', $donationNote->id)
                ->update([
                    'comment_content' => $donationNote->content,
                    'comment_parent' => $donationNote->donationId,
                    'comment_type' => 'donation',
                ]);

            if ($donationNote->isDirty('type') && $donationNote->type->isDonor()) {
                $this->upsertDonationNoteType($donationNote);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donation note', compact('donationNote'));

            throw new $exception('Failed updating a donation note');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_donation_note_updated', $donationNote);
    }

    /**
     * @since 2.21.0
     *
     * @param DonationNote $donationNote
     *
     * @return bool
     * @throws Exception
     */
    public function delete(DonationNote $donationNote): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_donation_note_deleting', $donationNote);

        try {
            DB::table('give_comments')
                ->where('comment_ID', $donationNote->id)
                ->delete();

            DB::table('give_commentmeta')
                ->where('give_comment_id', $donationNote->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donation note', compact('donationNote'));

            throw new $exception('Failed deleting a donation note');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_donation_note_deleted', $donationNote);

        return true;
    }

    /**
     * @since 2.21.0
     *
     * @param  int  $donationId
     *
     * @return ModelQueryBuilder
     */
    public function queryByDonationId(int $donationId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('comment_parent', $donationId)
            ->orderBy('comment_ID', 'DESC');
    }

    /**
     * @since 2.21.0
     *
     * @param DonationNote $donationNote
     *
     * @return void
     */
    private function validateDonationNote(DonationNote $donationNote)
    {
        foreach ($this->requiredDonationProperties as $key) {
            if (!isset($donationNote->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }

        if (!$donationNote->donation) {
            throw new InvalidArgumentException('Invalid donationId, Donation does not exist');
        }
    }

    /**
     * @return ModelQueryBuilder<DonationNote>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(DonationNote::class);

        return $builder->from('give_comments')
            ->select(
                ['comment_ID', 'id'],
                ['comment_parent', 'donationId'],
                ['comment_content', 'content'],
                ['comment_date', 'createdAt']
            )
            ->attachMeta(
                'give_commentmeta',
                'comment_ID',
                'give_comment_id',
                ...DonationNoteMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('comment_type', 'donation');
    }

    /**
     * @since 2.25.0
     */
    private function upsertDonationNoteType(DonationNote $donationNote)
    {
        $table = DB::table('give_commentmeta');

        $query = $table
            ->where('give_comment_id', $donationNote->id)
            ->where('meta_key', DonationNoteMetaKeys::TYPE)
            ->get();

        if (!$query) {
            $table->insert([
                'give_comment_id' => $donationNote->id,
                'meta_key' => DonationNoteMetaKeys::TYPE,
                'meta_value' => $donationNote->type->getValue(),
            ]);
        } else {
            $table
                ->where('give_comment_id', $donationNote->id)
                ->where('meta_key', DonationNoteMetaKeys::TYPE)
                ->update([
                    'meta_value' => $donationNote->type->getValue(),
                ]);
        }
    }
}
