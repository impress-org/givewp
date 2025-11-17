import { AdminSectionsWrapper } from "@givewp/components/AdminDetailsPage/AdminSection";
import { AdditionalInfoSlot } from "../../../slots";
import CustomFields from "./CustomFields";

/**
 * @since 4.6.0
 */
export default function DonationDetailsPageRecordsAdditionalInfoTab() {
    return (
        <AdminSectionsWrapper>
            <CustomFields />
            <AdditionalInfoSlot />
        </AdminSectionsWrapper>
    );
}
