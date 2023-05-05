import React, {ReactElement, useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {
    RecommendedProductData,
    useRecommendations,
} from '@givewp/components/ListTable/ProductRecommendation/useRecommendations';

/**
 * @unreleased
 */
export default function ProductRecommendation() {
    const {getRecommendation, removeRecommendation} = useRecommendations();
    const selectedOption = getRecommendation();
    const [showRecommendation, setShowRecommendation] = useState<boolean>(!!selectedOption);

    const closeMessage = async (async) => {
        await removeRecommendation({
            option: selectedOption.enum,
        });

        setShowRecommendation(false);
    };

    return showRecommendation && <RotatingMessage selectedOption={selectedOption} closeMessage={closeMessage} />;
}

/**
 * @unreleased
 */
interface RotatingMessageProps {
    selectedOption: RecommendedProductData;
    closeMessage: (async: any) => Promise<void>;
}

function RotatingMessage({selectedOption, closeMessage}: RotatingMessageProps) {
    const {message = '', documentationPage = '', innerHtml = ''} = selectedOption;

    return (
        <tr>
            <td colSpan={8}>
                <div className={styles.productRecommendation}>
                    <div className={styles.container}>
                        <div>
                            <img
                                src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`}
                            />

                            <TranslatedMessage message={message} />
                        </div>

                        <a target="_blank" href={documentationPage}>
                            {innerHtml}
                            <img
                                src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/external-link-icon.svg`}
                            />
                        </a>
                    </div>

                    <button onClick={closeMessage}>
                        <img
                            src={`${window.GiveDonations?.pluginUrl}/assets/dist/images/list-table/circular-exit-icon.svg`}
                        />
                    </button>
                </div>
            </td>
        </tr>
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
