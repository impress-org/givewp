import { useState, useEffect } from 'react';

import './style.scss';

const AvatarControl = ( { url, file, onChange } ) => {
	const [ highlight, setHighlight ] = useState( false );
	const [ previewSrc, setPreviewSrc ] = useState( url );

	useEffect( () => {
		if ( file ) {
			const reader = new window.FileReader();
			reader.readAsDataURL( file );
			reader.onloadend = function() {
				setPreviewSrc( reader.result );
			};
		}
	}, [ file ] );

	const handleDrop = ( evt ) => {
		// Prevent default behavior (Prevent file from being opened)
		evt.preventDefault();

		if ( evt.dataTransfer.items ) {
			// Use DataTransferItemList interface to access the file(s)
			// If dropped items aren't files, reject them
			if ( evt.dataTransfer.items[ 0 ].kind === 'file' ) {
				const newFile = evt.dataTransfer.items[ 0 ].getAsFile();
				if ( newFile.type.includes( 'image' ) ) {
					onChange( newFile );
				}
			}
		} else {
			// Use DataTransfer interface to access the file(s)
			const newFile = evt.dataTransfer.files[ 0 ];
			if ( newFile.type.includes( 'image' ) ) {
				onChange( newFile );
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
					{ file ? (
						<div className="give-donor-profile-avatar-control__instructions">
							{ file.name }
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
