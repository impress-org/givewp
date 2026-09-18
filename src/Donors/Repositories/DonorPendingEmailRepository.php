<?php

namespace Give\Donors\Repositories;

use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;

/**
 * Stores additional-email changes that are pending ownership verification.
 *
 * Each pending email is one give_donormeta row keyed by DonorMetaKeys::PENDING_EMAIL with
 * a value of ['token' => string, 'email' => string, 'createdAt' => int]. Entries expire
 * after the TTL and are pruned on every read/write; each donor may hold a limited number
 * of entries, which bounds verification emails sent on their behalf.
 *
 * @since TBD
 */
class DonorPendingEmailRepository
{
    /**
     * Default maximum number of pending emails per donor.
     *
     * @since TBD
     */
    const DEFAULT_LIMIT = 5;

    /**
     * Default seconds before a pending email expires.
     *
     * @since TBD
     */
    const DEFAULT_TTL = DAY_IN_SECONDS;

    /**
     * List a donor's pending emails, pruning expired entries.
     *
     * @since TBD
     *
     * @return array<int, array{token: string, email: string, createdAt: int}>
     */
    public function all(int $donorId): array
    {
        $rows = (array)give()->donor_meta->get_meta($donorId, DonorMetaKeys::PENDING_EMAIL, false);

        return array_values($this->pruneExpired($donorId, $this->validEntries($rows)));
    }

    /**
     * Queue an email for verification.
     *
     * Returns the verification token, or null when the email is invalid, already owned
     * (primary or additional) by any donor, already pending for this donor, or the
     * donor's pending limit is reached.
     *
     * @since TBD
     */
    public function add(int $donorId, string $email): ?string
    {
        if (!is_email($email) || Donor::whereEmail($email)) {
            return null;
        }

        $pending = $this->all($donorId);

        foreach ($pending as $entry) {
            if (hash_equals($entry['email'], $email)) {
                return null;
            }
        }

        $limit = (int)apply_filters('givewp_donor_pending_email_limit', self::DEFAULT_LIMIT, $donorId);

        if (count($pending) >= $limit) {
            return null;
        }

        $entry = [
            'token' => wp_generate_password(24, false),
            'email' => $email,
            'createdAt' => time(),
        ];

        give()->donor_meta->add_meta($donorId, DonorMetaKeys::PENDING_EMAIL, $entry);

        return $entry['token'];
    }

    /**
     * Find the pending entry that owns the token, pruning expired entries across all donors.
     *
     * @since TBD
     *
     * @return array{donorId: int, entry: array{token: string, email: string, createdAt: int}}|null
     */
    public function findByToken(string $token): ?array
    {
        $rows = DB::table('give_donormeta')
            ->select('donor_id', 'meta_value')
            ->where('meta_key', DonorMetaKeys::PENDING_EMAIL)
            ->getAll();

        foreach ($rows as $row) {
            $entry = maybe_unserialize($row->meta_value);

            if (!is_array($entry) || !$this->isValidEntry($entry)) {
                continue;
            }

            if ($this->isExpired($entry)) {
                $this->delete((int)$row->donor_id, $entry);
                continue;
            }

            if (hash_equals((string)$entry['token'], $token)) {
                return [
                    'donorId' => (int)$row->donor_id,
                    'entry' => $entry,
                ];
            }
        }

        return null;
    }

    /**
     * Delete a specific pending entry.
     *
     * @since TBD
     *
     * @param array{token: string, email: string, createdAt: int} $entry
     */
    public function delete(int $donorId, array $entry): bool
    {
        return (bool)give()->donor_meta->delete_meta($donorId, DonorMetaKeys::PENDING_EMAIL, $entry);
    }

    /**
     * @since TBD
     *
     * @param array<int, mixed> $rows
     *
     * @return array<int, array{token: string, email: string, createdAt: int}>
     */
    private function validEntries(array $rows): array
    {
        return array_values(array_filter($rows, function ($entry): bool {
            return is_array($entry) && $this->isValidEntry($entry);
        }));
    }

    /**
     * @since TBD
     */
    private function isValidEntry(array $entry): bool
    {
        return isset($entry['token'], $entry['email'], $entry['createdAt'])
            && is_string($entry['token'])
            && is_string($entry['email']);
    }

    /**
     * @since TBD
     *
     * @param array<int, array{token: string, email: string, createdAt: int}> $entries
     *
     * @return array<int, array{token: string, email: string, createdAt: int}>
     */
    private function pruneExpired(int $donorId, array $entries): array
    {
        foreach ($entries as $index => $entry) {
            if ($this->isExpired($entry)) {
                $this->delete($donorId, $entry);
                unset($entries[$index]);
            }
        }

        return $entries;
    }

    /**
     * @since TBD
     *
     * @param array{token: string, email: string, createdAt: int} $entry
     */
    private function isExpired(array $entry): bool
    {
        $ttl = (int)apply_filters('givewp_donor_pending_email_ttl', self::DEFAULT_TTL, $entry['email']);

        return (int)$entry['createdAt'] < time() - $ttl;
    }
}
