import {ReactNode} from 'react';

import './styles.scss';

type ColumnRowProps = {
    children: ReactNode;
};

/**
 * @unreleased
 */
export default function Row({children}: ColumnRowProps) {
    return <div className={'givewp-welcome-banner-row'}>{children}</div>;
}
