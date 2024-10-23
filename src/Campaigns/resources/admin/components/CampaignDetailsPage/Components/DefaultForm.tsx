import {__} from "@wordpress/i18n";
import HeaderText from './HeaderText';
import HeaderSubText from './HeaderSubText';
import {CampaignFormOption} from "@givewp/campaigns/admin/components/CampaignDetailsPage/types";

const DefaultFormWidget = ({forms}: {forms: CampaignFormOption[]}) => {
    return (

    <div style={{
        flex: 1,
        backgroundColor: 'white',
        padding: '20px',
        borderRadius: '8px',
    }}>
        <HeaderText>{__('Default campaign form')}</HeaderText>
        <HeaderSubText>{__('Your campaign page and blocks will collect donations through this form by default. You can change the default form at any time.')}</HeaderSubText>
        <select style={{
            fontSize: '14px',
            fontWeight: 500,
            lineHeight: '20px',
            marginTop: '20px',
            width: '100%',
            height: '48px',
            maxWidth: '100%', // Override `.wp-core-ui select` styles
        }}>
            {forms.map((form) => (
                <option key={form.id} value={form.id}>{form.title}</option>
            ))}
        </select>
    </div>
    )
}

export default DefaultFormWidget;
