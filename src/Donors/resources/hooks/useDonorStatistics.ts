import {useEntityRecord} from '@wordpress/core-data';
import {useState, useEffect} from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * @since 4.4.0
 */
export interface DonorStatistics {
    donations: {
        lifetimeAmount: number;
        highestAmount: number;
        averageAmount: number;
        count: number;
        first: {
            amount: string;
            date: string;
        } | null;
        last: {
            amount: string;
            date: string;
        } | null;
    };
    donorType: string;
    preferredPaymentMethod: string;
}

/**
 * @since 4.4.0
 */
export function useDonorStatistics(donorId: number, mode: 'live' | 'test' = 'live') {
    const [statistics, setStatistics] = useState<DonorStatistics | null>(null);
    const [error, setError] = useState<Error | null>(null);
    const [isResolving, setIsResolving] = useState<boolean>(false);
    const [hasResolved, setHasResolved] = useState<boolean>(false);

    useEffect(() => {
      if (!donorId) return;

      setIsResolving(true);
      setHasResolved(false);
      setError(null);

      apiFetch({
        path: `/givewp/v3/donors/${donorId}/statistics?mode=${mode}`,
        method: 'GET',
      })
        .then((data: DonorStatistics) => {
          setStatistics(data);
          setHasResolved(true);
        })
        .catch((err: Error) => {
          setError(err);
          setHasResolved(true);
        })
        .finally(() => {
          setIsResolving(false);
        });
    }, [donorId, mode]);

    return { statistics, error, isResolving, hasResolved };
  }
