import React, {useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {RecommendedProductData, useRecommendations} from './useRecommendations';

/**
 * @unreleased
 */

interface ProductRecommendationsProps {
    apiSettings: {table; pluginUrl};
}

export default function ProductRecommendations({apiSettings}: ProductRecommendationsProps) {
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

    return (
        <RotatingMessage
            columns={apiSettings.table.columns}
            pluginUrl={apiSettings.pluginUrl}
            selectedOption={selectedOption}
            closeMessage={closeMessage}
        />
    );
}

/**
 * @unreleased
 */
interface RotatingMessageProps {
    selectedOption: RecommendedProductData;
    closeMessage: (async: any) => Promise<void>;
    pluginUrl: string;
    columns: Array<{visible: boolean}>;
}

function RotatingMessage({selectedOption, closeMessage, pluginUrl, columns}: RotatingMessageProps) {
    const {message = '', documentationPage = '', innerHtml = ''} = selectedOption;

    const visibleColumns = columns?.filter((column) => column.visible || column.visible === undefined);

    return (
        <tr>
            <td colSpan={visibleColumns.length + 1}>
                <div className={styles.productRecommendation}>
                    <div className={styles.container}>
                        <div>
                            <img src={`${pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`} />

                            <TranslatedMessage message={message} />
                        </div>

                        <a target="_blank" href={documentationPage}>
                            {innerHtml}
                            <img src={`${pluginUrl}/assets/dist/images/list-table/external-link-icon.svg`} />
                        </a>
                    </div>

                    <button onClick={closeMessage}>
                        <img src={`${pluginUrl}/assets/dist/images/list-table/circular-exit-icon.svg`} />
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
    const Message = () => <p>{message}</p>;

    const translatedString = createInterpolateElement(__('<strong>ProTip: </strong> <message />', 'give'), {
        strong: <strong />,
        message: <Message />,
    });

    return translatedString;
}
