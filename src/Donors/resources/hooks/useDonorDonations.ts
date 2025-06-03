import apiFetch from '@wordpress/api-fetch';
import {useEffect, useState} from '@wordpress/element';

/**
 * @unreleased
 */
interface DonationResponse {
    id: number;
    formTitle: string;
    createdAt: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    status: string;
    amount: {
        value: string;
        valueInMinorUnits: string;
        currency: string;
    };
}

/**
 * @unreleased
 */
interface DonationsQueryParams {
    donorId: number;
    page?: number;
    perPage?: number;
    mode?: 'test' | 'live';
}

/**
 * @unreleased
 */
interface DonationsHookReturn {
    data: DonationResponse[] | undefined;
    isLoading: boolean;
    error: Error | null;
}

/**
 * @unreleased
 * TODO: Refactor
 */
export function useDonorDonations({
    donorId,
    page = 1,
    perPage = 5,
    mode = 'test',
}: DonationsQueryParams): DonationsHookReturn {
    const [data, setData] = useState<DonationResponse[]>();
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<Error | null>(null);

    useEffect(() => {
        const fetchData = async () => {
            if (donorId <= 0) {
                return;
            }

            setIsLoading(true);
            setError(null);

            try {
                const donationsResponse = await apiFetch<DonationResponse[]>({
                    path: `/givewp/v3/donations?donorId=${donorId}&page=${page}&per_page=${perPage}&mode=${mode}`,
                });

                setData(donationsResponse);
            } catch (err) {
                setError(err instanceof Error ? err : new Error('Failed to fetch data'));
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, [donorId, page, perPage, mode]);

    return {data, isLoading, error};
}
