/**
 * Internal dependencies
 */
import './style.scss';

const PlaceholderAnimation = () => {
	return (
		<div className="placeholder-animation">
			<div className="timeline-wrapper">
				<div className="timeline-item">
					<div className="animated-background">

						<div className="layer label layer-4">
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
							<div className="layer-item opaque"></div>
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
						</div>
						<div className="layer-gap small"></div>

						<div className="layer h2 layer-5">
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
							<div className="layer-item opaque"></div>
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
						</div>
						<div className="layer-gap medium"></div>

						<div className="layer label layer-6">
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
						</div>
						<div className="layer-gap small"></div>

						<div className="layer h2 layer-7">
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
						</div>

						<div className="layer-gap medium"></div>
						<div className="layer-gap medium"></div>

						<div className="layer h1 layer-8">
							<div className="layer-item opaque"></div>
							<div className="layer-item"></div>
							<div className="layer-item opaque"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default PlaceholderAnimation;
