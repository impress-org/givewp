import {ReactElement, useState} from 'react';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
// import {
//     RecommendedProductData,
//     useRecommendations,
// } from '@givewp/components/ListTable/ProductRecommendation/useRecommendations';
import {getWindowData} from '../../utils';

import './style.scss';

/**
 * @unreleased
 */
export default function ProductRecommendation() {
    // const {getRecommendation, removeRecommendation} = useRecommendations();
    const [showRecommendation, setShowRecommendation] = useState<boolean>(true);

    const pluginUrl = getWindowData('pluginUrl');

    const closeMessage = async (async) => {
        // await removeRecommendation({
        //     option: selectedOption.enum,
        // });

        setShowRecommendation(false);
    };

    return (
        showRecommendation && (
            <div className={'givewp-reports-recurring-recommendation'}>
                <div className={'givewp-reports-recurring-recommendation-container'}>
                    <div>
                        <img src={`${pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`} />

                        <TranslatedMessage />
                    </div>

                    <a target="_blank" href={'https://docs.givewp.com/subscriptions'}>
                        {__('Get more donations')}
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
function TranslatedMessage() {
    const ProTip = <strong>Pro Tip: </strong>;

    const Message: () => ReactElement<string> = () => {
        return createInterpolateElement(
            __('<ProTip/> Increase your fundraising revenue by over 30% with recurring giving campaigns.'),
            {ProTip}
        );
    };

    return <Message />;
}
