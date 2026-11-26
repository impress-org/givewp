import {__} from '@wordpress/i18n';
import './styles.scss';

/**
 * The neutral "Add-on" tag: what it sits on needs an add-on the site does not have.
 *
 * @since TBD
 */
export default function AddOnTag() {
    return <span className="givewp-add-on-tag">{__('Add-on', 'give')}</span>;
}
