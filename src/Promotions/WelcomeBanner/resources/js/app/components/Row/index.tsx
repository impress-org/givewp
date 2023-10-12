import {ReactNode} from 'react';

import './styles.scss';

type RowProps = {
    children: ReactNode;
};

/**
 * @since 3.0.0
 */
export default function Row({children}: RowProps) {
    return <div className={'givewp-welcome-banner-row'}>{children}</div>;
}
