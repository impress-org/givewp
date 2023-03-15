import PaymentInformation from '../PaymentInformation';

import {FormTemplateProps} from './types';

/**
 *
 * @unreleased
 */

export default function FormTemplate({data}: FormTemplateProps) {
    return (
        <>
            <PaymentInformation data={data} />
        </>
    );
}
