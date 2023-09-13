import {useRef, useState} from 'react';
import './styles.scss';
import getWindowData from '../../../index';

type VideoPlayerProps = {
    src: string;
    fallbackImage: string;
};

/**
 * @since 3.0.0
 */
export default function VideoPlayer({src, fallbackImage}: VideoPlayerProps) {
    const videoRef = useRef<HTMLVideoElement>(null);
    const [isPlaying, setIsPlaying] = useState<boolean>(false);

    const {assets} = getWindowData();

    const togglePlay = () => {
        if (videoRef.current.paused) {
            videoRef.current.play();
            setIsPlaying(true);
        } else {
            videoRef.current.pause();
            setIsPlaying(false);
        }
    };

    const useFallbackImage =
        src === null ||
        src === undefined ||
        src === '' ||
        isPlaying === null ||
        (!src.endsWith('.mp4') && !src.endsWith('.mov'));

    return (
        <div className={'givewp-welcome-banner-video'}>
            <div className={'givewp-welcome-banner-video-container'}>
                {useFallbackImage ? (
                    <div className={'givewp-welcome-banner-video-fallback'}>
                        <img className={'givewp-welcome-banner-video-fallback__image'} src={fallbackImage} alt={'/'} />
                    </div>
                ) : (
                    <video ref={videoRef} src={src} loop muted />
                )}

                {!useFallbackImage && (
                    <button className="play-button" onClick={togglePlay}>
                        {isPlaying ? (
                            <img src={`${assets}/pause-icon.svg`} alt="Pause" />
                        ) : (
                            <img src={`${assets}/play-icon.svg`} alt={'play'} />
                        )}
                    </button>
                )}
            </div>
        </div>
    );
}
