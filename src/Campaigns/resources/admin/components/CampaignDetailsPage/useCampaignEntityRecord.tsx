import {useEntityRecord} from "@wordpress/core-data";
import {Campaign} from "@givewp/campaigns/admin/components/types";

const urlParams = new URLSearchParams(window.location.search);
const campaignId = urlParams.get('id');

/**
 * @unreleased
 */
export default () => {
    const {
        record: campaign,
        hasResolved,
        save,
        edit,
    }: {
        record: Campaign;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Campaign) => void;
    } = useEntityRecord('givewp', 'campaign', campaignId);

    return {campaign, hasResolved, save, edit};
}
