import { useCallback, useState, useEffect } from 'react';
import { useDropzone } from 'react-dropzone';

import './style.scss';

const AvatarControl = ( { url, file, onChange } ) => {
	const onDrop = useCallback( acceptedFiles => {
		onChange( acceptedFiles[ 0 ] );
	}, [] );

	const { getRootProps, getInputProps, isDragActive } = useDropzone( { onDrop } );
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

	return (
		<div className="give-donor-profile-avatar-control">
			<label className="give-donor-profile-avatar-control__label">
				Avatar
			</label>
			<div className="give-donor-profile-avatar-control__input" { ...getRootProps() }>
				<input { ...getInputProps() } />
				<div className="give-donor-profile-avatar-control__preview">
					<img src={ previewSrc } />
				</div>
				<div className={ `give-donor-profile-avatar-control__dropzone${ isDragActive ? ' give-donor-profile-avatar-control__dropzone--highlight' : '' }` }>
					<div className="give-donor-profile-avatar-control__instructions">
						{
							isDragActive ? (
								<p>Drop the files here ...</p>
							) : (
								<p>Drag n drop some files here, or click to <span className="give-donor-profile-avatar-control__select-link">select files</span></p>
							)
						}
					</div>
				</div>
			</div>
		</div>
	);
};

export default AvatarControl;
