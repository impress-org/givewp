import React, {useState} from 'react';
import styles from './style.module.scss';
import {__} from '@wordpress/i18n';
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
 * @since 4.10.0 Refactor html structure to use a single p tag with a span and a link.
 * @since 2.27.1
 */
function RotatingMessage({selectedOption, closeMessage, pluginUrl, columns}: RotatingMessageProps) {
    const {message = '', documentationPage = '', innerHtml = ''} = selectedOption;

    const visibleColumns = columns?.filter((column) => column.visible || column.visible === undefined);

    return (
        <tr className={styles.productRecommendationRow}>
            <td colSpan={visibleColumns.length + 1}>
                <div className={styles.productRecommendation}>
                    <img src={`${pluginUrl}build/assets/dist/images/list-table/light-bulb-icon.svg`} />

                    <p className={styles.message}>
                        <strong>{__('PRO TIP: ', 'give')}</strong>
                        <span>{message}</span>
                        <a target="_blank" href={documentationPage}>
                            {innerHtml}
                            <img src={`${pluginUrl}build/assets/dist/images/list-table/external-link-icon.svg`} />
                        </a>
                    </p>

                    <button onClick={closeMessage}>
                        <img src={`${pluginUrl}build/assets/dist/images/list-table/circular-exit-icon.svg`} />
                    </button>
                </div>
            </td>
        </tr>
    );
}
