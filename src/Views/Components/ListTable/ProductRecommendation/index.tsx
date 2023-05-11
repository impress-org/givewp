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

export default function ProductRecommendation({columns}: { columns: number }) {
    const {getRecommendation, removeRecommendation} = useRecommendations();
    const selectedOption = getRecommendation();
    const [showRecommendation, setShowRecommendation] = useState<boolean>(!!selectedOption);

    const closeMessage = async (async) => {
        setShowRecommendation(false);

        await removeRecommendation({
            option: selectedOption.enum,
        });
    };

    if (!showRecommendation) {
        return null;
    }

    return <RotatingMessage columns={columns} selectedOption={selectedOption} closeMessage={closeMessage} />;
}

/**
 * @unreleased
 */
interface RotatingMessageProps {
    selectedOption: RecommendedProductData;
    closeMessage: (async: any) => Promise<void>;
    columns: number;
}

function RotatingMessage({selectedOption, closeMessage, columns}: RotatingMessageProps) {
    const {message = '', documentationPage = '', innerHtml = ''} = selectedOption;

    return (
        <tr>
            <td colSpan={columns}>
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
