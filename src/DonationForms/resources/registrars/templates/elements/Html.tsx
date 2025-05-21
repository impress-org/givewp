import type {HtmlProps} from '@givewp/forms/propTypes';
import styles from '../styles.module.scss';

export default function Html({html}: HtmlProps) {
    return <div className={styles.htmlField} dangerouslySetInnerHTML={{__html: html}} />;
}
