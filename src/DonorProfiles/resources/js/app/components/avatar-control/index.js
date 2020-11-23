import './style.scss';

const AvatarControl = () => {
	return (
		<div className="give-donor-profile-avatar-control">
			<div className="give-donor-profile-avatar-control__label">
				Avatar
			</div>
			<div className="give-donor-profile-avatar-control__input">
				<div className="give-donor-profile-avatar-control__preview">
					<img src="https://cdn.vox-cdn.com/thumbor/ClK0Ing_P9O6kLoQGzbzWleylws=/1400x1050/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/19892155/robin_hoo.jpeg" />
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
