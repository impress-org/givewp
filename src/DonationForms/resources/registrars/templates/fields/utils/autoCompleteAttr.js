/**
 * User agents sometimes have features for helping users fill forms in, for example prefilling
 * the user's address based on earlier user input. The autocomplete content attribute can be
 * used to hint to the user agent how to, or indeed whether to, provide such a feature.
 *
 * @since 4.3.0
 *
 * @see https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill
 */
export default function autoCompleteAttr(inputName) {
    const autoCompleteMapping = {
        company: 'organization',
        honorific: 'honorific-prefix',
        firstName: 'given-name',
        lastName: 'family-name',
        email: 'email',
        phone: 'tel',
        country: 'country',
        address1: 'address-line1',
        address2: 'address-line2',
        city: 'address-level2',
        state: 'address-level1',
        zip: 'postal-code',
    };

    return autoCompleteMapping[inputName];
}
