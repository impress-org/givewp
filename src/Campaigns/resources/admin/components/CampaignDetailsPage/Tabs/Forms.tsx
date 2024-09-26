import {getGiveCampaignDetailsWindowData} from '../index';
import DonationFormsListTable from '../../../../../../DonationForms/V2/resources/components/DonationFormsListTable';

const {adminUrl} = getGiveCampaignDetailsWindowData();
const urlParams = new URLSearchParams(window.location.search);

{
    /*<div>
            <div>Forms</div>
            <p>{`${adminUrl}edit.php?post_type=give_forms&page=give-forms&campaign-id=${urlParams.get('id')}`}</p>
            <br />
            <IframeResizer
                src={`${adminUrl}edit.php?post_type=give_forms&page=give-forms&campaign-id=${urlParams.get('id')}`}
                checkOrigin={false}
                style={{
                    width: '1px',
                    minWidth: '100%',
                    border: '0',
                }}
            />
        </div>*/
}
//<DonationFormsListTable />

/**
 * @unreleased
 */
export default () => {
    return <DonationFormsListTable />;
};

/*export default () => {
    return <CampaignsListTable />;
};*/
