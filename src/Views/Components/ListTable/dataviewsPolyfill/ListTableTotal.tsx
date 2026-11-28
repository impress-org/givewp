import {_n} from '@wordpress/i18n';
import {insertNumbers} from '../formatNumber';

/**
 * The footer message of the next DataViews pin (12.0.1, WordPress 7.0), without the selection case: "30
 * of 464 Items" when more rows match than are on the page, else "30 Items". It uses the stock count's
 * class and the same strings as DataViews (core's text domain), so it looks and reads the same.
 *
 * Polyfill: see README.md in this folder.
 *
 * @since TBD
 */
export default function ListTableTotal({rows, totalItems}: {rows: number; totalItems: number}) {
    // The message is translated first and the numbers go in after, so the strings stay the same as core's
    // and keep their translations. The plural uses the real number.
    const message =
        totalItems > rows
            ? insertNumbers(
                  /* translators: %1$d: number of items. %2$d: total number of items. */
                  _n('%1$d of %2$d Item', '%1$d of %2$d Items', totalItems),
                  {'%1$d': rows, '%2$d': totalItems}
              )
            : insertNumbers(
                  /* translators: %d: number of items. */
                  _n('%d Item', '%d Items', rows),
                  {'%d': rows}
              );

    return <span className="givewp-list-table__total dataviews-bulk-actions-footer__item-count">{message}</span>;
}
