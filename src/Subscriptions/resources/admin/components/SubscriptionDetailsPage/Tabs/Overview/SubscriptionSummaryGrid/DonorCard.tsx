import {__} from '@wordpress/i18n';
import {GridCard} from '@givewp/admin/components/Grid';
import {Donor} from '@givewp/donors/admin/components/types';
import styles from './styles.module.scss';

/**
 * @since 4.10.0
 */
export default function DonorCard({donor}: {donor: Donor}) {

    return (
        <GridCard heading={__('Associated donor', 'give')} headingId="donor">
                <>
                    {donor ? (
                        <>
                            <a
                                className={styles.donorLink}
                                href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donor.id}`}
                            >
                                {donor.name}
                            </a>
                            <p>{donor.email}</p>
                        </>
                    ) : (
                        <p>{__('No donor associated with this subscription', 'give')}</p>
                    )}
                </>
        </GridCard>
    );
}
