/**
 * External Dependencies
 */
import { getDonorOptionsWindowData } from "@givewp/donors/utils";

/**
 * Internal Dependencies
 */
import { DonorAddress } from "../../../../types";

/**
 * @since 4.4.0
 */
interface FormattedAddressProps {
    address: DonorAddress;
}

/**
 * @since 4.4.0
 */
export default function FormattedAddress({ address }: FormattedAddressProps) {
    const { countries } = getDonorOptionsWindowData();

    const formatCityStateZip = (): string => {
        const parts = [address.city, address.state, address.zip].filter(Boolean);

        if (parts.length === 0) return '';

        // If we have a city, add comma separation for readability
        if (address.city && parts.length > 1) {
            return `${address.city}, ${parts.slice(1).join(' ')}`;
        }

        return parts.join(' ');
    };

    const getCountryName = (): string => {
        if (!address.country) return '';
        return countries[address.country] || address.country;
    };

    const cityStateZipString = formatCityStateZip();
    const countryName = getCountryName();

    return (
        <>
            {address.address1 && <span>{address.address1}</span>}
            {address.address2 && <span>{address.address2}</span>}
            {cityStateZipString && <span>{cityStateZipString}</span>}
            {countryName && <span>{countryName}</span>}
        </>
    );
}
