import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import { RecordsSlot } from '../../slots';

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageRecordsTab() {
    return (
        <>
            <AdminSectionsWrapper>
                <RecordsSlot />
            </AdminSectionsWrapper>
        </>
    );
}
