import { AdminSectionsWrapper } from "@givewp/components/AdminDetailsPage/AdminSection";
import { AdditionalInfoSlot } from "../../../slots";
import CustomFields from "./CustomFields";

/**
 * @unreleased
 */
export default function DonationDetailsPageRecordsAdditionalInfoTab() {
    return (
        <AdminSectionsWrapper>
            <CustomFields />
            <AdditionalInfoSlot />
        </AdminSectionsWrapper>
    );
}
