import './style.scss';

const Tooltip = ( { title, body, position } ) => {
	const style = position ? {
		top: position.y,
		left: position.x,
	} : null;

	return (
		<div className="givewp-tooltip" style={ style }>
			{ title && (
				<div className="givewp-tooltip__header">
					{ title }
				</div>
			) }
			<div className="givewp-tooltip__body">
				{ body }
			</div>
			<div className="givewp-tooltip__footer"></div>
			<div className="givewp-tooltip__caret"></div>
		</div>
	);
};
export default Tooltip;
