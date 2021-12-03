import './style.scss';

const Table = ({header, rows, footer}) => {
    return (
        <div className="give-donor-dashboard-table">
            <div className="give-donor-dashboard-table__header">{header}</div>
            <div className="give-donor-dashboard-table__rows">{rows}</div>
            <div className="give-donor-dashboard-table__footer">{footer}</div>
        </div>
    );
};

export default Table;
