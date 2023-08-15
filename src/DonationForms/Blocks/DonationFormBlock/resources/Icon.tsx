// Temporary fork from '@givewp/components/GiveIcon'

const colorMap = {
    white: '#fff',
    grey: '#555d66',
    give: '#66bb6a',
};

/**
 * Give Icon
 */
export default function GiveIcon({color = 'grey', size = '24px', ...rest}) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 157.2 157.2" width={size} height={size} {...rest}>
            <circle fill={colorMap[color]} cx="78.6" cy="78.6" r="78.6"/>
            <path
                fill="#fff"
                d="M89.8 84.2c.3.7 1 1.3 1 1.3 13.9 1.7 33.6-.2 48.6-2.2-8.6 18.5-24 30.8-38.1 30.8-26.5 0-46.9-32.1-46.9-32.1 8.2-7.2 21.7-30.8 41.2-30.8s28 10.7 28 10.7l2.2-3.5s-9.1-31.9-34.9-31.9-53.2 42.3-69.2 52c0 0 22 52.2 70.2 52.2 40.4 0 50.6-38.6 52.5-48.2 5.4-.8 9.9-1.6 12.8-2.1 1-2.2 2.1-6.1 1.3-11.3-16.1 6.2-40.5 13.2-69.1 13.2-.1 0 0 1 .4 1.9z"
            />
        </svg>
    );
}
