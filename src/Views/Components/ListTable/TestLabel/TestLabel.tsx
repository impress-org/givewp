import {__} from "@wordpress/i18n";
import styles from "./TestLabel.module.scss";
import cx from "classnames";

export default function TestLabel ({className = '', ...rest}) {
    return (
        <div className={cx(styles.test, className)} {...rest}>
            {__('test', 'give')}
            <span className={'give-visually-hidden'}>{__(' donation', 'give')}</span>
        </div>
    );
}
