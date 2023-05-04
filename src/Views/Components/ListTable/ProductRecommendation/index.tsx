import React, {ReactElement, useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';

export default function ProductRecommendation() {
    const [showRecommendation, setShowRecommendation] = useState<boolean>(true);

    const recommendedAddons = {
        // ToDo: Use UTM links for documentationPage
        recurring: {
            documentationPage: '',
            tip: 'Increase your fundraising revenue by over 30% with recurring giving campaigns.',
            innerHtml: __('Get More Donations', 'give'),
        },
        feeRecovery: {
            documentationPage: '',
            tip: 'Maximize your total donated income to 100% by providing donors with the option to cover the credit card processing fees.',
            innerHtml: __('Get More Donations', 'give'),
        },
        designatedFunds: {
            documentationPage: ' ',
            tip: 'Elevate your fundraising campaigns with multiple forms, unlimited donation funds, and tailored fundraising reports.',
            innerHtml: __('Start creating designated funds', 'give'),
        },
    };

    const options = [recommendedAddons.recurring, recommendedAddons.feeRecovery, recommendedAddons.designatedFunds];
    const randomIndex = Math.floor(Math.random() * 3);
    const selectedOption = options[randomIndex];
    const {tip, documentationPage, innerHtml} = selectedOption;

    const ProTip = <strong>{__('Pro Tip: ', 'give')}</strong>;
    const Recommendation = <p>{tip}</p>;

    const TranslatedMessage: () => ReactElement<string> = () => {
        return createInterpolateElement(__('<ProTip/> <Recommendation/>'), {ProTip, Recommendation});
    };

    return (
        showRecommendation && (
            <tr>
                <td colSpan={8}>
                    <div className={styles.productRecommendation}>
                        <div className={styles.container}>
                            <div>
                                <img
                                    src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`}
                                />

                                <TranslatedMessage />
                            </div>

                            <a target="_blank" href={documentationPage}>
                                {innerHtml}
                                <img
                                    src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/external-link-icon.svg`}
                                />
                            </a>
                        </div>
                        <button onClick={() => setShowRecommendation(false)}>
                            <img
                                src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/circular-exit-icon.svg`}
                            />
                        </button>
                    </div>
                </td>
            </tr>
        )
    );
}
