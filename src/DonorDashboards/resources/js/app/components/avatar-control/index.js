import {useCallback, useState, useEffect} from 'react';
import {__} from '@wordpress/i18n';
import {useDropzone} from 'react-dropzone';

import './style.scss';

const AvatarControl = ({url, file, onChange}) => {
    const onDrop = useCallback((acceptedFiles) => {
        onChange(acceptedFiles[0]);
    }, []);

    const {getRootProps, getInputProps, isDragActive} = useDropzone({
        onDrop,
        accept: 'image/jpeg, image/png, image/gif',
        maxFiles: 1,
    });
    const [previewSrc, setPreviewSrc] = useState(url);

    useEffect(() => {
        if (file) {
            const reader = new window.FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function () {
                setPreviewSrc(reader.result);
            };
        }
    }, [file]);

    return (
        <div className="give-donor-dashboard-avatar-control">
            <label className="give-donor-dashboard-avatar-control__label">{__('Avatar', 'give')}</label>
            <div className="give-donor-dashboard-avatar-control__input" {...getRootProps()}>
                <input {...getInputProps()} />
                <div className="give-donor-dashboard-avatar-control__preview">
                    <img src={previewSrc}/>
                </div>
                <div
                    className={`give-donor-dashboard-avatar-control__dropzone${
                        isDragActive ? ' give-donor-dashboard-avatar-control__dropzone--highlight' : ''
                    }`}
                >
                    <div className="give-donor-dashboard-avatar-control__instructions">
                        {isDragActive ? (
                            <p>{__('Drop the image here...', 'give')}</p>
                        ) : (
                            <p>
                                {__('Drag image here to set', 'give')} <br/>
                                {__('avatar or', 'give')}{' '}
                                <span className="give-donor-dashboard-avatar-control__select-link">
                                    {__('find image', 'give')}
                                </span>
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AvatarControl;
