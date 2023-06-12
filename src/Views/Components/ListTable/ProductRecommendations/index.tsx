import React, {useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {RecommendedProductData, useRecommendations} from '@givewp/promotions/hooks/useRecommendations';

interface ProductRecommendationsProps {
    apiSettings: {table; pluginUrl};
    options: any;
}

/**
 * @since 2.27.1
 */
export default function ProductRecommendations({apiSettings, options}: ProductRecommendationsProps) {
    const {getRandomRecommendation, getRecommendation, removeRecommendation} = useRecommendations(apiSettings, options);
    const selectedOption = apiSettings === window.GiveDonors ? getRecommendation() : getRandomRecommendation();
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
 * @since 2.27.1
 */
function RotatingMessage({selectedOption, closeMessage, pluginUrl, columns}: RotatingMessageProps) {
    const {message = '', documentationPage = '', innerHtml = ''} = selectedOption;

    const visibleColumns = columns?.filter((column) => column.visible || column.visible === undefined);

    return (
        <tr>
            <td colSpan={visibleColumns.length + 1}>
                <div className={styles.productRecommendation}>
                    <div className={styles.container}>
                            <img src={`${pluginUrl}/assets/dist/images/list-table/light-bulb-icon.svg`} />

                            <TranslatedMessage message={message} />

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
 * @since 2.27.1
 */
function TranslatedMessage({message}: TranslatedMessageProps) {

    const translatedString = createInterpolateElement(__('<strong>PRO TIP: </strong> <message />', 'give'), {
        strong: <strong />,
        message: <p className={styles.message}>{message}</p>,
    });

    return translatedString;
}
