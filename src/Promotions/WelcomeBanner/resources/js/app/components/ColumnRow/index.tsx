import {ReactNode} from 'react';

import './styles.scss';

type ColumnRowProps = {
    children: ReactNode;
};

export default function ColumnRow({children}: ColumnRowProps) {
    return <div className={'givewp-welcome-banner-col-row'}>{children}</div>;
}
