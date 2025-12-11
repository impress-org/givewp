
import {Donation} from '../admin/components/types';

/**
 * @since 4.13.0
 */
export default function getDonationEmbeds(donation: Donation) {
    return {
        campaign: donation?._embedded?.['givewp:campaign']?.[0],
        donor: donation?._embedded?.['givewp:donor']?.[0],
        form: donation?._embedded?.['givewp:form']?.[0],
    };
}
