import {__} from "@wordpress/i18n";
import HeaderText from './HeaderText';
import HeaderSubText from './HeaderSubText';
import {CampaignFormOption} from "@givewp/campaigns/admin/components/CampaignDetailsPage/types";

/**
 * @unreleased
 */
const DefaultFormWidget = ({defaultForm}: {defaultForm: string}) => {
    return (
        <div style={{
            flex: 1,
            display: 'flex',
            flexDirection: 'column',
            gap: '24px',
            backgroundColor: 'white',
            padding: '16px 12px 24px 24px',
            borderRadius: '8px',
        }}>
            <div style={{
                display: 'flex',
                alignItems: 'start',
                justifyContent: 'space-between',
            }}>
                <div>
                    <HeaderText>{__('Default campaign form', '')}</HeaderText>
                    <HeaderSubText>{__('Your campaign page and blocks will collect donations through this form by default.', '')}</HeaderSubText>
                </div>
                <a href='#' style={{
                    fontSize: '14px',
                    color: '#060c1a',
                    fontWeight: 500,
                    backgroundColor: '#e5e7eb',
                    padding: '8px 16px',
                    borderRadius: '8px',
                    textDecoration: 'none',
                }}>
                    {__('Edit', '')}
                </a>
            </div>
            <div style={{
                fontWeight: 500,
                backgroundColor: '#f9fafb',
                border: '1px solid #e5e7eb',
                padding: '12px 16px',
                borderRadius: '4px',
            }}>
                {defaultForm}
            </div>
        </div>
    )
}

export default DefaultFormWidget;
