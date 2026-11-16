<?php

namespace Give\Donations\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationCounters;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Throwable;
use WP_Post;

/**
 * Keeps the donation counters up to date on every donation write.
 *
 * Each write path fires a hook before and after the write. Before, the donation's bucket is read
 * from the database (unless this request already knows it); after, it is read again and the
 * donation moves from the old bucket to the new one. Reading the bucket instead of trusting the
 * hook's arguments makes every handler idempotent: when one change fires several hooks (the model
 * hook, then the legacy hook it dispatches, then the post hooks), the first one moves the donation
 * and the others find nothing left to move. So nothing is counted twice.
 *
 * Old state is only ever read, never guessed. An after hook without its before hook moves the
 * donation only when it is a create hook (the donation is new). Anything else marks the counters
 * dirty, which schedules a recount within about a minute and stops the screens from using the
 * counters until it ran. The old status a legacy hook passes is not trusted alone: the same hook
 * delivered again would move the donation twice.
 *
 * A counter update never makes a write fail: every handler catches its own errors and marks the
 * counters dirty instead.
 *
 * Covered: the model (create, update, delete, trash, untrash), the legacy Give_Payment and the
 * functions built on it (status changes, bulk actions, the CSV importer, gateways), the WordPress
 * post API (wp_insert_post, wp_update_post, wp_trash_post, wp_untrash_post, wp_delete_post) and the
 * donation metadata API for the mode and subscription keys. Not covered: raw SQL; the scheduled
 * rebuild repairs that.
 *
 * Between a write's before and after hooks the request holds a "writing" options row, so the
 * rebuild never takes its snapshot while a write is committed but not yet counted.
 *
 * @since TBD
 */
class SyncDonationCounters
{
    /**
     * Meta keys whose change can move a donation to another bucket.
     */
    private const TRACKED_META_KEYS = [DonationMetaKeys::MODE, DonationMetaKeys::SUBSCRIPTION_ID];

    /**
     * Bucket of each donation as the counters count it now; null for a donation not counted.
     *
     * @var array<int, string|null>
     */
    private $known = [];

    /**
     * Open writes by key, with their nesting depth.
     *
     * @var array<string, int>
     */
    private $open = [];

    /**
     * How many model creates are between their creating and created hooks.
     *
     * @var int
     */
    private $creating = 0;

    /**
     * Donations a model write is changing (between its before and after hooks), with the depth.
     *
     * @var array<int, int>
     */
    private $modelWrites = [];

    /**
     * Meta changes skipped during a model create for donations other than the one created.
     *
     * @var array<int, true>
     */
    private $skippedDuringCreate = [];

    /**
     * @var string|null name of this request's writing row
     */
    private $writingRow;

    /**
     * @var bool
     */
    private $markedDirtyWhileDisabled = false;

    /**
     * Registers the handlers on every write path.
     *
     * @since TBD
     */
    public function register(): void
    {
        $this->registerModelHooks();
        $this->registerPostHooks();
        $this->registerMetaHooks();
        $this->registerLegacyHooks();
    }

    /**
     * @since TBD
     */
    private function registerModelHooks(): void
    {
        add_action('givewp_donation_creating', function () {
            $this->guard(function () {
                $this->creating++;
                $this->open('model-new');
            });
        });

        // Before the legacy bridge (priority 10) dispatches give_insert_payment for the same donation.
        add_action('givewp_donation_created', function (Donation $donation) {
            $this->guard(function () use ($donation) {
                $this->creating = max(0, $this->creating - 1);
                $this->sync((int)$donation->id, ['created' => true]);
                $this->close('model-new');
                $this->settleSkippedDuringCreate((int)$donation->id);
            });
        }, 1);

        foreach (['updating', 'deleting', 'trashing', 'untrashing'] as $before) {
            add_action("givewp_donation_{$before}", function (Donation $donation) {
                $this->guard(function () use ($donation) {
                    $this->modelWrites[(int)$donation->id] = ($this->modelWrites[(int)$donation->id] ?? 0) + 1;
                    $this->before((int)$donation->id);
                });
            });
        }

        foreach (['updated', 'deleted', 'trashed', 'untrashed'] as $after) {
            add_action("givewp_donation_{$after}", function (Donation $donation) {
                $this->guard(function () use ($donation) {
                    $this->endModelWrite((int)$donation->id);
                    $this->after((int)$donation->id);
                });
            });
        }
    }

    /**
     * @since TBD
     */
    private function endModelWrite(int $donationId): void
    {
        if (isset($this->modelWrites[$donationId]) && --$this->modelWrites[$donationId] <= 0) {
            unset($this->modelWrites[$donationId]);
        }
    }

    /**
     * A meta change skipped during a model create belongs to that create when it is the created
     * donation. Any other one was a change to another donation that was not counted.
     *
     * @since TBD
     */
    private function settleSkippedDuringCreate(int $createdId): void
    {
        unset($this->skippedDuringCreate[$createdId]);

        if ($this->creating === 0 && $this->skippedDuringCreate) {
            $this->skippedDuringCreate = [];
            DonationCounters::markDirty();
        }
    }

    /**
     * @since TBD
     */
    private function registerPostHooks(): void
    {
        add_filter('wp_insert_post_data', function ($data, $postarr = [], $unsanitized = [], $update = null) {
            $isNew = $update === null ? empty($postarr['ID']) : !$update;

            if ($isNew && isset($data['post_type']) && 'give_payment' === $data['post_type']) {
                $this->guard(function () {
                    $this->open('post-new');
                });
            }

            return $data;
        }, 10, 4);

        add_action('save_post_give_payment', function ($postId, $post = null, $update = true) {
            if ($update) {
                return;
            }

            $this->guard(function () use ($postId) {
                $this->sync((int)$postId, ['created' => true]);
                $this->close('post-new');
            });
        }, 10, 3);

        add_action('pre_post_update', function ($postId, $data = []) {
            if (isset($data['post_type']) && 'give_payment' === $data['post_type']) {
                $this->guard(function () use ($postId) {
                    $this->before((int)$postId);
                });
            }
        }, 10, 2);

        add_action('post_updated', function ($postId, $postAfter = null, $postBefore = null) {
            if (!$this->isDonationPost($postAfter) && !$this->isDonationPost($postBefore)) {
                return;
            }

            $this->guard(function () use ($postId) {
                $this->after((int)$postId);
            });
        }, 10, 3);

        add_action('before_delete_post', function ($postId, $post = null) {
            if ($this->isDonationPost($post ?: get_post($postId))) {
                $this->guard(function () use ($postId) {
                    $this->before((int)$postId);
                });
            }
        }, 10, 2);

        add_action('deleted_post', function ($postId, $post = null) {
            if ($this->isDonationPost($post) || isset($this->open[(string)(int)$postId])) {
                $this->guard(function () use ($postId) {
                    $this->after((int)$postId);
                });
            }
        }, 10, 2);
    }

    /**
     * The mode and subscription keys, through the WordPress metadata API (Give_Payment, the importer,
     * add-ons). The donation meta type is "payment" until the 2.2.0 rename upgrade has run.
     *
     * @since TBD
     */
    private function registerMetaHooks(): void
    {
        foreach (['donation', 'payment'] as $metaType) {
            add_action("add_{$metaType}_meta", function ($objectId, $metaKey) {
                $this->beforeMeta((int)$objectId, (string)$metaKey);
            }, 10, 2);

            foreach (['update', 'delete'] as $action) {
                add_action("{$action}_{$metaType}_meta", function ($metaIds, $objectId, $metaKey) {
                    $this->beforeMeta((int)$objectId, (string)$metaKey);
                }, 10, 3);
            }

            foreach (['added', 'updated', 'deleted'] as $action) {
                add_action("{$action}_{$metaType}_meta", function ($metaIds, $objectId, $metaKey) {
                    $this->afterMeta((int)$objectId, (string)$metaKey);
                }, 10, 3);
            }
        }
    }

    /**
     * The legacy hooks, for writes that skip the hooks above. They also fire after model writes,
     * through the legacy bridge, and after the post hooks of Give_Payment; then the donation is
     * already counted and nothing moves. Alone, they mark the counters dirty: they carry no state
     * that could tell a new change from the same one delivered again.
     *
     * @since TBD
     */
    private function registerLegacyHooks(): void
    {
        add_action('give_insert_payment', function ($donationId) {
            $this->guard(function () use ($donationId) {
                $this->sync((int)$donationId);
            });
        });

        add_action('give_update_payment_status', function ($donationId) {
            $this->guard(function () use ($donationId) {
                $this->sync((int)$donationId);
            });
        });

        add_action('give_payment_deleted', function ($donationId) {
            $this->guard(function () use ($donationId) {
                $this->sync((int)$donationId);
            });
        });
    }

    /**
     * @since TBD
     */
    private function beforeMeta(int $donationId, string $metaKey): void
    {
        if (!$this->tracksMeta($donationId, $metaKey)) {
            return;
        }

        $this->guard(function () use ($donationId) {
            $this->before($donationId);
        });
    }

    /**
     * @since TBD
     */
    private function afterMeta(int $donationId, string $metaKey): void
    {
        if (!$this->tracksMeta($donationId, $metaKey)) {
            return;
        }

        $this->guard(function () use ($donationId) {
            $this->after($donationId);
        });
    }

    /**
     * Whether a meta change is counted here. A model write changes the meta inside its database
     * transaction and counts the donation in its after hook, once committed, so the meta hooks of
     * that donation are skipped: a counter update inside the transaction could deadlock with
     * another and roll back the donation write itself. During a model create the new donation is not
     * known yet, so an unknown donation's meta is skipped then.
     *
     * @since TBD
     */
    private function tracksMeta(int $donationId, string $metaKey): bool
    {
        if ($donationId <= 0 || !in_array($metaKey, self::TRACKED_META_KEYS, true)) {
            return false;
        }

        if (isset($this->modelWrites[$donationId])) {
            return false;
        }

        if ($this->creating > 0 && !array_key_exists($donationId, $this->known)) {
            $this->skippedDuringCreate[$donationId] = true;

            return false;
        }

        return true;
    }

    /**
     * Before a write: opens it and remembers the donation's bucket.
     *
     * @since TBD
     */
    private function before(int $donationId): void
    {
        $this->open((string)$donationId);

        if (!array_key_exists($donationId, $this->known)) {
            $this->known[$donationId] = $this->classify($donationId);
        }
    }

    /**
     * After a write: moves the donation and closes the write.
     *
     * @since TBD
     */
    private function after(int $donationId): void
    {
        $this->sync($donationId);
        $this->close((string)$donationId);
    }

    /**
     * Moves the donation from the bucket the counters count it in to the one it belongs to now.
     *
     * @since TBD
     *
     * @param array{created?: bool} $hints what the hook says about the old state
     */
    private function sync(int $donationId, array $hints = []): void
    {
        if ($donationId <= 0) {
            return;
        }

        $current = $this->classify($donationId);

        if (array_key_exists($donationId, $this->known)) {
            $old = $this->known[$donationId];
        } elseif (!empty($hints['created'])) {
            $old = null;
        } else {
            // The old bucket is unknown, so the counters may be off by this donation.
            $this->known[$donationId] = $current;
            DonationCounters::markDirty();

            return;
        }

        $this->known[$donationId] = $current;

        if ($old === $current) {
            return;
        }

        $deltas = [];

        if ($old !== null) {
            $deltas[$old] = -1;
        }

        if ($current !== null) {
            $deltas[$current] = ($deltas[$current] ?? 0) + 1;
        }

        DonationCounters::add($deltas);
    }

    /**
     * The donation's bucket from the database, or null when it is not a donation (deleted).
     *
     * @since TBD
     */
    private function classify(int $donationId): ?string
    {
        global $wpdb;

        $meta = DB::prefix('give_donationmeta');

        $row = DB::get_row(DB::prepare(
            "SELECT post_type, post_status,
                EXISTS(SELECT 1 FROM {$meta} WHERE donation_id = ID AND meta_key = %s AND meta_value = %s) AS test,
                EXISTS(SELECT 1 FROM {$meta} WHERE donation_id = ID AND meta_key = %s AND meta_value >= '1') AS recurring
             FROM {$wpdb->posts} WHERE ID = %d",
            DonationMetaKeys::MODE,
            DonationMode::TEST,
            DonationMetaKeys::SUBSCRIPTION_ID,
            $donationId
        ));

        if (!$row || 'give_payment' !== $row->post_type) {
            return null;
        }

        return DonationCounters::bucket(
            $row->test ? 'test' : 'live',
            $row->recurring ? 'recurring' : 'onetime',
            (string)$row->post_status
        );
    }

    /**
     * Opens a write. The first open write of the request takes the writing row.
     *
     * @since TBD
     */
    private function open(string $key): void
    {
        if (!$this->open) {
            $this->holdWritingRow();
        }

        $this->open[$key] = ($this->open[$key] ?? 0) + 1;
    }

    /**
     * Closes a write. When none is left open, the writing row is released.
     *
     * @since TBD
     */
    private function close(string $key): void
    {
        if (!isset($this->open[$key])) {
            return;
        }

        if (--$this->open[$key] <= 0) {
            unset($this->open[$key]);
        }

        if (!$this->open) {
            $this->releaseWritingRow();
        }
    }

    /**
     * @since TBD
     */
    private function holdWritingRow(): void
    {
        global $wpdb;

        if ($this->writingRow === null) {
            $this->writingRow = DonationCounters::WRITING_PREFIX . md5(uniqid((string)mt_rand(), true));
            add_action('shutdown', [$this, 'releaseAll'], 0);
        }

        DB::query(DB::prepare(
            "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')
             ON DUPLICATE KEY UPDATE option_value = %s",
            $this->writingRow,
            (string)time(),
            (string)time()
        ));
    }

    /**
     * @since TBD
     */
    private function releaseWritingRow(): void
    {
        global $wpdb;

        if ($this->writingRow === null) {
            return;
        }

        DB::query(DB::prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s", $this->writingRow));
    }

    /**
     * Releases a write left open by a write that failed between its hooks, and marks the counters
     * dirty when a model create that never finished made the request skip meta changes. Hooked to
     * shutdown.
     *
     * @since TBD
     */
    public function releaseAll(): void
    {
        if ($this->skippedDuringCreate && DonationCounters::isEnabled()) {
            $this->skippedDuringCreate = [];
            DonationCounters::markDirty();
        }

        if (!$this->open) {
            return;
        }

        $this->open = [];

        try {
            $this->releaseWritingRow();
        } catch (Throwable $exception) {
            // The rebuild ignores a writing row once it has expired.
        }
    }

    /**
     * Runs a handler; on any error marks the counters dirty instead, so the write goes on.
     *
     * @since TBD
     */
    private function guard(callable $handler): void
    {
        if (!DonationCounters::isEnabled()) {
            // Writes made while the counters are off are not counted; mark them dirty once, so turning
            // them back on recounts before they are used.
            if (!$this->markedDirtyWhileDisabled) {
                $this->markedDirtyWhileDisabled = true;
                DonationCounters::markDirty(false);
            }

            return;
        }

        try {
            $handler();
        } catch (Throwable $exception) {
            DonationCounters::markDirty();
        }
    }

    /**
     * @since TBD
     *
     * @param mixed $post
     */
    private function isDonationPost($post): bool
    {
        return $post instanceof WP_Post && 'give_payment' === $post->post_type;
    }

    /**
     * Forgets what this request knows. For tests and long-running processes.
     *
     * @since TBD
     */
    public function reset(): void
    {
        $this->releaseAll();
        $this->known = [];
        $this->creating = 0;
        $this->modelWrites = [];
        $this->skippedDuringCreate = [];
        $this->markedDirtyWhileDisabled = false;
    }
}
