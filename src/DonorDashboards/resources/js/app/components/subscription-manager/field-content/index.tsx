import './style.scss';

export function FieldContent({classNames, children}) {
    return <div className={`give-donor-dashboard-field-content ${classNames}`}>{children}</div>;
}
