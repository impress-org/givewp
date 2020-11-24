import './style.scss';

const AvatarControl = ( { value } ) => {
	return (
		<div className="give-donor-profile-avatar-control">
			<div className="give-donor-profile-avatar-control__label">
				Avatar
			</div>
			<div className="give-donor-profile-avatar-control__input">
				<div className="give-donor-profile-avatar-control__preview">
					{ value && (
						<img src={ value } />
					) }
				</div>
				<div className="give-donor-profile-avatar-control__dropzone">
					<div className="give-donor-profile-avatar-control__instructions">
						Drag avatar here to set avatar or <span className="give-donor-profile-avatar-control__select-link">find avatar</span>
					</div>
				</div>
			</div>
		</div>
	);
};
export default AvatarControl;
