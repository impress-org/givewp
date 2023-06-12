import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {getWindowData} from '../../utils';
import './style.scss';
import {RecommendedProductData, useRecommendations} from '@givewp/promotions/hooks/useRecommendations';

/**
 * @since 2.27.1
 */
declare global {
    interface Window {
        giveReportsData: {
            legacyReportsUrl: string;
            allTimeStart: string;
            currencies: string[];
            currency: string;
            testMode: boolean;
            pluginUrl: string;
            dismissedRecommendations: string[];
            apiRoot: string;
            apiNonce: string;
        };
    }
}

/**
 * @since 2.27.1
 */
interface ReportsPageRecommendations {
    recurring: RecommendedProductData;
    feeRecovery: RecommendedProductData;
}

const RecommendationConfig: ReportsPageRecommendations = {
    recurring: {
        enum: 'givewp_reports_recurring_recommendation_dismissed',
        documentationPage: ' https://docs.givewp.com/recurring-reports',
        message: __('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give'),
        innerHtml: __('Get More Donations', 'give'),
    },
    feeRecovery: {
        enum: 'givewp_reports_fee_recovery_recommendation_dismissed',
        documentationPage: 'https://docs.givewp.com/feerecovery-reports',
        message: __(
            '90% of donors opt to give more to help cover transaction fees when given the opportunity. Give donors that opportunity.',
            'give'
        ),
        innerHtml: __('Get the Fee Recovery add-on today', 'give'),
    },
};

/**
 * @since 2.27.1
 */
export default function ProductRecommendations() {
    const {removeRecommendation, getRecommendation} = useRecommendations(window.giveReportsData, [
        RecommendationConfig.recurring,
        RecommendationConfig.feeRecovery,
    ]);
    const selectedOption = getRecommendation();
    const [showRecommendation, setShowRecommendation] = useState<boolean>(!!selectedOption);

    const pluginUrl = getWindowData('pluginUrl');

    const closeMessage = async (async) => {
        await removeRecommendation({option: selectedOption.enum});
        setShowRecommendation(false);
    };

    if (!showRecommendation) {
        return null;
    }

    return (
        <div className={'givewp-reports-recommendation'}>
            <div className={'givewp-reports-recommendation-container'}>
                <div>
                    <img src={`${pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`} />

                    <TranslatedMessage message={selectedOption?.message} />
                </div>

                <a target="_blank" href={selectedOption?.documentationPage}>
                    {selectedOption?.innerHtml}
                    <img src={`${pluginUrl}/assets/dist/images/list-table/external-link-icon.svg`} />
                </a>
            </div>

            <button onClick={closeMessage}>
                <img src={`${pluginUrl}/assets/dist/images/close-icon.svg`} />
            </button>
        </div>
    );
}

/**
 * @since 2.27.1
 */
type TranslatedMessageProps = {message: string};

function TranslatedMessage({message}: TranslatedMessageProps) {
    const translatedString = createInterpolateElement(__('<strong>PRO TIP: </strong> <message />', 'give'), {
        strong: <strong />,
        message: <p>{message}</p>,
    });

    return translatedString;
}
