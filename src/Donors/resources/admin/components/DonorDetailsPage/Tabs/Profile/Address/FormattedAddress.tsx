/**
 * External Dependencies
 */
import { getDonorOptionsWindowData } from "@givewp/donors/utils";

/**
 * Internal Dependencies
 */
import { DonorAddress } from "../../../../types";

/**
 * @unreleased
 */
export default function FormattedAddress({address}: {address: DonorAddress}) {
    const { countries } = getDonorOptionsWindowData();

    const cityStateZip = [
        address.city,
        address.state,
        address.zip
    ].filter(Boolean).join(' ');

    const cityStateZipString = address.city ? cityStateZip.replace(' ', ', ') : cityStateZip;

    const countryName = address.country && countries[address.country]
        ? countries[address.country]
        : address.country;

    return (
        <>
            {address.address1 && <span>{address.address1}</span>}
            {address.address2 && <span>{address.address2}</span>}
            {cityStateZip && <span>{cityStateZipString}</span>}
            {countryName && <span>{countryName}</span>}
        </>
    );
}