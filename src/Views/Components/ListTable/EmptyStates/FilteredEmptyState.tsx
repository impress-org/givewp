import {__, sprintf} from '@wordpress/i18n';
import {Button} from '@wordpress/components';
import './styles.scss';

/**
 * @since TBD
 */
export interface FilteredEmptyStateProps {
    pluralName: string;
    onClearFilters: () => void;
}

/**
 * Shown when a search or filter matches no records, so an empty result never reads like a site
 * with no data. A site with no records at all keeps the screen's onboarding blank slate.
 *
 * @since TBD
 */
export default function FilteredEmptyState({pluralName, onClearFilters}: FilteredEmptyStateProps) {
    return (
        <div className="givewp-filtered-empty-state" role="status">
            {/* translators: %s: record type, plural (for example "donations") */}
            <p>{sprintf(__('No %s match your search or filters.', 'give'), pluralName)}</p>
            <Button variant="secondary" onClick={onClearFilters}>
                {__('Clear filters', 'give')}
            </Button>
        </div>
    );
}
