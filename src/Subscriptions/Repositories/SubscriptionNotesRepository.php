<?php

namespace Give\Subscriptions\Repositories;

use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\ValueObjects\SubscriptionNoteMetaKeys;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;

/**
 * NOTE: This repository is still using the old comments table.
 * In the future, we will migrate to the new comments table.
 *
 * @since 4.8.0
 */
class SubscriptionNotesRepository
{
    /**
     * @since 4.8.0
     *
     * @var string[]
     */
    private $requiredSubscriptionProperties = [
        'subscriptionId',
        'content',
    ];

    /**
     * @since 4.8.0
     */
    private const COMMENT_TYPE = 'give_sub_note';

    /**
     * @since 4.8.0
     */
    public function getById(int $noteId): ?SubscriptionNote
    {
        return $this->prepareQuery()
            ->where('comments.comment_ID', $noteId)
            ->get();
    }

    /**
     * @since 4.8.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(SubscriptionNote $subscriptionNote): void
    {
        if (! $subscriptionNote->type) {
            $subscriptionNote->type = SubscriptionNoteType::ADMIN();
        }

        $this->validateSubscriptionNote($subscriptionNote);

        Hooks::doAction('givewp_subscription_note_creating', $subscriptionNote);

        $dateCreated = Temporal::withoutMicroseconds($subscriptionNote->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);

        DB::query('START TRANSACTION');

        try {
            DB::table('comments')
                ->insert([
                    'comment_content' => $subscriptionNote->content,
                    'comment_date' => $dateCreatedFormatted,
                    'comment_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'comment_post_ID' => $subscriptionNote->subscriptionId,
                    'comment_type' => self::COMMENT_TYPE,
                    'user_id' => is_admin() ? get_current_user_id() : 0,
                ]);

            $commentId = DB::last_insert_id();

            if ($subscriptionNote->type->isDonor()) {
                DB::table('commentmeta')
                    ->insert([
                        'comment_ID' => $commentId,
                        'meta_key' => SubscriptionNoteMetaKeys::TYPE,
                        'meta_value' => SubscriptionNoteType::DONOR,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a subscription note', compact('subscriptionNote'));

            throw new $exception('Failed creating a subscription note');
        }

        DB::query('COMMIT');

        $subscriptionNote->id = $commentId;
        $subscriptionNote->createdAt = $dateCreated;

        Hooks::doAction('givewp_subscription_note_created', $subscriptionNote);
    }

    /**
     * @since 4.8.0
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(SubscriptionNote $subscriptionNote): void
    {
        $this->validateSubscriptionNote($subscriptionNote);

        Hooks::doAction('givewp_subscription_note_updating', $subscriptionNote);

        DB::query('START TRANSACTION');

        try {
            DB::table('comments')
                ->where('comment_ID', $subscriptionNote->id)
                ->update([
                    'comment_content' => $subscriptionNote->content,
                    'comment_post_ID' => $subscriptionNote->subscriptionId,
                    'comment_type' => self::COMMENT_TYPE,
                    'user_id' => is_admin() ? get_current_user_id() : 0,
                ]);

            if ($subscriptionNote->isDirty('type') && $subscriptionNote->type->isDonor()) {
                $this->upsertSubscriptionNoteType($subscriptionNote);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a subscription note', compact('subscriptionNote'));

            throw new $exception('Failed updating a subscription note');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_subscription_note_updated', $subscriptionNote);
    }

    /**
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function delete(SubscriptionNote $subscriptionNote): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_subscription_note_deleting', $subscriptionNote);

        try {
            DB::table('comments')
                ->where('comment_ID', $subscriptionNote->id)
                ->delete();

            DB::table('commentmeta')
                ->where('comment_ID', $subscriptionNote->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a subscription note', compact('subscriptionNote'));

            throw new $exception('Failed deleting a subscription note');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_subscription_note_deleted', $subscriptionNote);

        return true;
    }

    /**
     * @since 4.8.0
     */
    public function queryBySubscriptionId(int $subscriptionId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('comments.comment_post_ID', $subscriptionId)
            ->orderBy('comments.comment_ID', 'DESC');
    }

    /**
     * @since 4.8.0
     */
    private function validateSubscriptionNote(SubscriptionNote $subscriptionNote): void
    {
        foreach ($this->requiredSubscriptionProperties as $key) {
            if (! isset($subscriptionNote->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }

        if (! $subscriptionNote->subscription) {
            throw new InvalidArgumentException('Invalid subscriptionId, Subscription does not exist');
        }
    }

    /**
     * @since 4.8.0
     *
     * @return ModelQueryBuilder<SubscriptionNote>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(SubscriptionNote::class);

        return $builder->from('comments', 'comments')
            ->select(
                ['comments.comment_ID', 'id'],
                ['comments.comment_post_ID', 'subscriptionId'],
                ['comments.comment_content', 'content'],
                ['comments.comment_date', 'createdAt']
            )
            ->attachMeta(
                'commentmeta',
                'comments.comment_ID',
                'comment_ID',
                ...SubscriptionNoteMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('comments.comment_type', self::COMMENT_TYPE);
    }

    /**
     * @since 4.8.0
     */
    private function upsertSubscriptionNoteType(SubscriptionNote $subscriptionNote): void
    {
        $table = DB::table('commentmeta');

        $query = $table
            ->where('comment_ID', $subscriptionNote->id)
            ->where('meta_key', SubscriptionNoteMetaKeys::TYPE)
            ->get();

        if (! $query) {
            $table->insert([
                'comment_ID' => $subscriptionNote->id,
                'meta_key' => SubscriptionNoteMetaKeys::TYPE,
                'meta_value' => $subscriptionNote->type->getValue(),
            ]);
        } else {
            $table
                ->where('comment_ID', $subscriptionNote->id)
                ->where('meta_key', SubscriptionNoteMetaKeys::TYPE)
                ->update([
                    'meta_value' => $subscriptionNote->type->getValue(),
                ]);
        }
    }
}
