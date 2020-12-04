import { useState, useEffect } from 'react';

import './style.scss';

const AvatarControl = ( { storedValue, value, onChange } ) => {
	const [ highlight, setHighlight ] = useState( false );
	const [ previewSrc, setPreviewSrc ] = useState( storedValue );

	useEffect( () => {
		if ( value ) {
			const reader = new window.FileReader();
			reader.readAsDataURL( value );
			reader.onloadend = function() {
				setPreviewSrc( reader.result );
			};
		}
	}, [ value ] );

	const handleDrop = ( evt ) => {
		// Prevent default behavior (Prevent file from being opened)
		evt.preventDefault();

		if ( evt.dataTransfer.items ) {
			// Use DataTransferItemList interface to access the file(s)
			// If dropped items aren't files, reject them
			if ( evt.dataTransfer.items[ 0 ].kind === 'file' ) {
				const file = evt.dataTransfer.items[ 0 ].getAsFile();
				if ( file.type.includes( 'image' ) ) {
					onChange( evt.dataTransfer.files[ 0 ] );
				}
			}
		} else {
			// Use DataTransfer interface to access the file(s)
			const file = evt.dataTransfer.files[ 0 ];
			if ( file.type.includes( 'image' ) ) {
				onChange( evt.dataTransfer.files[ 0 ] );
			}
		}
		setHighlight( false );
	};

	const handleDragOver = ( evt ) => {
		// Prevent default behavior (Prevent file from being opened)
		evt.preventDefault();
	};

	return (
		<div className="give-donor-profile-avatar-control">
			<div className="give-donor-profile-avatar-control__label">
				Avatar
			</div>
			<div className="give-donor-profile-avatar-control__input">
				<div className="give-donor-profile-avatar-control__preview">
					{ previewSrc && (
						<img src={ previewSrc } />
					) }
				</div>
				<div
					className={ `give-donor-profile-avatar-control__dropzone${ highlight ? ' give-donor-profile-avatar-control__dropzone--highlight' : '' }` }
					onDrop={ ( evt ) => handleDrop( evt ) }
					onDragOver={ ( evt ) => handleDragOver( evt ) }
					onDragEnter={ () => setHighlight( true ) }
					onDragLeave={ () => setHighlight( false ) }
				>
					{ value ? (
						<div className="give-donor-profile-avatar-control__instructions">
							{ value.name }
						</div>
					) : (
						<div className="give-donor-profile-avatar-control__instructions">
							Drag avatar here to set avatar or <span className="give-donor-profile-avatar-control__select-link">find avatar</span>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};
export default AvatarControl;
