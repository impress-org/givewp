import { useState } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * @unreleased
 */
export function useDonationDelete() {
    const [error, setError] = useState<Error | null>(null);
    const [isResolving, setIsResolving] = useState(false);
    const [hasResolved, setHasResolved] = useState(false);

    async function deleteDonation(donationId: number) {
      if (!donationId) return;

      setIsResolving(true);
      setHasResolved(false);
      setError(null);

      try {
        await apiFetch({
          path: `/givewp/v3/donations/${donationId}`,
          method: 'DELETE',
        });

        setHasResolved(true);
      } catch (err) {
        setError(err as Error);
        setHasResolved(true);
      } finally {
        setIsResolving(false);
      }
    }

    return {
      deleteDonation,
      error,
      isResolving,
      hasResolved,
    };
  }
