import './styles.scss';

type SpotLightProps = {
    title: string | JSX.Element;
    description: string;
    children: any;
};

/**
 * @since 3.0.0
 */
export default function SpotLight({title, description, children}: SpotLightProps) {
    return (
        <div className={'givewp-welcome-banner-spotlight-container'}>
            {children}
            <div className={'givewp-welcome-banner-spotlight-container__details'}>
                <h2>{title}</h2>
                <p>{description}</p>
            </div>
        </div>
    );
}
