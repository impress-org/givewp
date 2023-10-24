import {__} from "@wordpress/i18n";
import {Button} from "@wordpress/components";

const RecurringDonationsPromo = () => {
    return <div
        style={{
            fontSize: '12px',
            fontWeight: '500',
            display: 'flex',
            flexDirection: 'column',
            gap: '12px',
            padding: '12px 16px 19px',
            borderRadius: '5px',
            boxShadow: '0 2px 2px 0 rgba(221, 221, 221, 0.25)',
            border: 'solid 0.5px var(--givewp-primary-700)'
        }}
    >
        <div style={{}}>
            {__('Provide donors the option of making flexible recurring donations.', 'give')}
        </div>
        <div style={{display: 'flex', justifyContent: 'space-around'}}>
            <Button href='https://givewp.com/addons/recurring-donations' target="_blank" rel="noopener noreferrer" variant={'primary'} style={{backgroundColor:'var(--givewp-primary-500)', padding: '4px 8px', height: 'auto'}}>Upgrade your plan</Button>
            <Button href='https://givewp.com/addons/recurring-donations' target="_blank" rel="noopener noreferrer" variant={'link'} style={{color: 'var(--givewp-gray-100)'}}>Read more</Button>
        </div>
    </div>
}

export default RecurringDonationsPromo;

