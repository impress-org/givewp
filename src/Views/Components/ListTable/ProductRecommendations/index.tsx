import React, {useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {RecommendedProductData, useRecommendations} from './useRecommendations';

interface ProductRecommendationsProps {
    apiSettings: {table; pluginUrl};
    options: any;
}

/**
 * @unreleased
 */
export default function ProductRecommendations({apiSettings, options}: ProductRecommendationsProps) {
    const {getRecommendation, removeRecommendation} = useRecommendations(apiSettings, options);
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

interface RotatingMessageProps {
    selectedOption: RecommendedProductData;
    closeMessage: (async: any) => Promise<void>;
    pluginUrl: string;
    columns: Array<{visible: boolean}>;
}

/**
 * @unreleased
 */
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

type TranslatedMessageProps = {message: string};

/**
 * @unreleased
 */
function TranslatedMessage({message}: TranslatedMessageProps) {
    const Message = () => <p>{message}</p>;

    const translatedString = createInterpolateElement(__('<strong>ProTip: </strong> <message />', 'give'), {
        strong: <strong />,
        message: <Message />,
    });

    return translatedString;
}
