import {__} from '@wordpress/i18n';
import {GridCard} from '@givewp/admin/components/Grid';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import styles from './styles.module.scss';

/**
 * @since 4.10.0
 */
export default function CampaignCard({campaign}: {campaign: Campaign}) {

    return (
        <GridCard heading={__('Campaign name', 'give')} headingId="campaign-name">
            {campaign && (
                <a
                    href={`edit.php?post_type=give_forms&page=give-campaigns&id=${campaign?.id}&tab=overview`}
                    className={styles.campaignLink}
                >
                    {campaign?.title}
                </a>
            )}
        </GridCard>
    );
}
