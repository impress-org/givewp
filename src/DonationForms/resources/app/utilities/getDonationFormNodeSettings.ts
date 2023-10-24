import {Form} from '@givewp/forms/types';

export default function getDonationFormNodeSettings(form: Form) {
    const {nodes, ...donationFormNode} = form;

    return donationFormNode;
}