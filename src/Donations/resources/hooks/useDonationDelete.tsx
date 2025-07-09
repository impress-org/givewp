import { useState } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * @unreleased
 */
// Hook
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
          path: `give-api/v2/admin/donations/delete?ids=${donationId}`,
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
  