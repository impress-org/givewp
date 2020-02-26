import './style.scss';

const Tooltip = ( { title, body } ) => {
	return (
		<div className="givewp-tooltip">
			<div className="givewp-tooltip__header">
				{ title }
			</div>
			<div className="givewp-tooltip__body">
				{ body }
			</div>
			<div className="givewp-tooltip__footer"></div>
			<div className="givewp-tooltip__caret"></div>
		</div>
	);
};
export default Tooltip;
