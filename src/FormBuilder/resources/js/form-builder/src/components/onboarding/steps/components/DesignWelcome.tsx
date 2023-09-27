import DesignCard from "@givewp/form-builder/components/onboarding/steps/components/DesignCard";
import {__} from "@wordpress/i18n";

const DesignWelcome = () => {
    return <div style={{display: 'flex', flexDirection: 'column', gap: 'var(--givewp-spacing-10)', margin: '0 auto var(--givewp-spacing-4)'}}>
        <div>
            <h3
                style={{
                    fontSize: '20px',
                    margin: 'var(--givewp-spacing-3) 0 var(--givewp-spacing-2) 0',
                    // @ts-ignore
                    textWrap: 'balance',
                }}
            >
                {__('Choose your form design', 'give')}
            </h3>
            <p style={{fontSize: '14px'}}>
                {__('Select one that suits your taste and requirements for your cause.', 'give')}
            </p>
        </div>

        <div style={{ display: 'grid', gridAutoFlow: 'column', columnGap: 'var(--givewp-spacing-10)'}}>
            <label className={'onboarding-set-design js-onboarding-set-design-classic'}>
                <input type="radio" name="designId" value="classic" checked />
                <DesignCard
                    title={__('Classic', 'give')}
                    description={__('This displays all form fields on one page. Donors fill out the form as they scroll down the page', 'give')}
                />
            </label>
            <label className={'onboarding-set-design js-onboarding-set-design-multi-step'}>
                <input type="radio" name="designId" value="multi-step" />
                <DesignCard
                    title={__('Multi-step', 'give')}
                    description={__('This walks the donor through a number of steps to the donation process. The sections are broken into steps in the form', 'give')}
                />
            </label>
        </div>
    </div>;
}

export default DesignWelcome;
