import {ReactElement, useState} from 'react';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {getWindowData} from '../../utils';
import './style.scss';
import {
    RecommendedProductData,
    useRecommendations,
} from '../../../../../../../src/Promotions/sharedResources/hooks/useRecommendations';

declare global {
    interface Window {
        giveReportsData: {
            legacyReportsUrl: string;
            allTimeStart: string;
            currencies: string[];
            currency: string;
            testMode: boolean;
            pluginUrl: string;
            recommendRecurringAddon: string;
            dismissedRecommendations: string[];
            apiRoot: string;
            apiNonce: string;
        };
    }
}

/**
 * @unreleased
 */
interface ReportsPageRecommendations {
    recurring: RecommendedProductData;
    feeRecovery: RecommendedProductData;
}

const RecommendationConfig: ReportsPageRecommendations = {
    // ToDo: Use UTM links for documentationPage
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
            'Maximize your fundraising revenue to 100% and increase the impact of your cause by providing donors the option to cover credit card processing fees.',
            'give'
        ),

        innerHtml: __('Get Fee Recovery', 'give'),
    },
};

/**
 * @unreleased
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

    return (
        showRecommendation && (
            <div className={'givewp-reports-recommendation'}>
                <div className={'givewp-reports-recommendation-container'}>
                    <div>
                        <img src={`${pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`} />

                        <TranslatedMessage message={selectedOption.message} />
                    </div>

                    <a target="_blank" href={'https://docs.givewp.com/subscriptions'}>
                        {selectedOption.innerHtml}
                        <img src={`${pluginUrl}/assets/dist/images/list-table/external-link-icon.svg`} />
                    </a>
                </div>

                <button onClick={closeMessage}>
                    <img src={`${pluginUrl}/assets/dist/images/close-icon.svg`} />
                </button>
            </div>
        )
    );
}

/**
 * @unreleased
 */
type TranslatedMessageProps = {message: string};

function TranslatedMessage({message}: TranslatedMessageProps) {
    const ProTip = <strong>Pro Tip: </strong>;
    const Recommendation = <p>{message}</p>;

    const Message: () => ReactElement<string> = () => {
        return createInterpolateElement(__('<ProTip/> <Recommendation/>'), {ProTip, Recommendation});
    };

    return <Message />;
}

