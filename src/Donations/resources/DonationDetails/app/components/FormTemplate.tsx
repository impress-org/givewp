import {useFormContext} from 'react-hook-form';
import PaymentInformation from './PaymentInformation';

/**
 *
 * @unreleased
 */

export default function FormTemplate({data}: any) {
    const methods = useFormContext();
    const {register, setValue} = methods;

    const {errors} = methods.formState;

    return (
        <>
            <PaymentInformation
                register={register}
                setValue={setValue}
                amount={data?.amount}
                feeAmountRecovered={data?.feeAmountRecovered}
                createdAt={data?.createdAt}
                time={data?.createdAt}
                form={{id: data?.formId, title: 'testTitle'}}
                status={data?.status}
                type={data?.type}
                gateway={data?.gateway}
            />
        </>
    );
}
