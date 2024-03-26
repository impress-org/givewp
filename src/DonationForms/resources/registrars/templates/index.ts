import TextField from './fields/Text';
import PasswordField from './fields/Password';
import TextAreaField from './fields/TextArea';
import EmailField from './fields/Email';
import HiddenField from './fields/Hidden';
import CheckboxField from './fields/Checkbox';
import RadioField from './fields/Radio';
import MultiSelectField from './fields/MultiSelect';
import DateField from './fields/Date';
import PhoneField from './fields/Phone';
import FileField from './fields/File';
import UrlField from './fields/Url';
import HtmlElement from './elements/Html';
import DonationSummaryElement from './elements/DonationSummary';
import NameGroup from './groups/Name';
import BillingAddressGroup from './groups/BillingAddress';
import DonationAmountGroup from './groups/DonationAmount';
import SectionLayout from './layouts/Section';
import Form from './layouts/Form';
import AmountField from './fields/Amount';
import ConsentField from './fields/Consent';
import SelectField from './fields/Select';
import Gateways from './fields/Gateways';
import Authentication from './groups/Authentication';
import Paragraph from './elements/Paragraph';
import FieldLabel from './layouts/FieldLabel';
import FieldDescription from './layouts/FieldDescription';
import FieldError from './layouts/FieldError';
import Header from './layouts/Header';
import HeaderTitle from './layouts/HeaderTitle';
import HeaderDescription from './layouts/HeaderDescription';
import Goal from './layouts/Goal';
import GoalAchieved from './layouts/GoalAchieved';
import NodeWrapper from './layouts/NodeWrapper';
import DonationReceipt from './layouts/DonationReceipt';
import MultiStepForm from './layouts/MultiStepForm';
import DonationSummaryItems from './layouts/DonationSummaryItems';
import FormError from './layouts/FormError';
import HeaderImage from './layouts/HeaderImage';

const defaultFormTemplates = {
    fields: {
        amount: AmountField,
        consent: ConsentField,
        text: TextField,
        password: PasswordField,
        textarea: TextAreaField,
        email: EmailField,
        hidden: HiddenField,
        gateways: Gateways,
        select: SelectField,
        checkbox: CheckboxField,
        radio: RadioField,
        multiSelect: MultiSelectField,
        date: DateField,
        phone: PhoneField,
        file: FileField,
        url: UrlField,
    },
    elements: {
        paragraph: Paragraph,
        html: HtmlElement,
        donationSummary: DonationSummaryElement,
    },
    groups: {
        name: NameGroup,
        donationAmount: DonationAmountGroup,
        authentication: Authentication,
        billingAddress: BillingAddressGroup,
    },
    layouts: {
        wrapper: NodeWrapper,
        section: SectionLayout,
        form: Form,
        multiStepForm: MultiStepForm,
        fieldLabel: FieldLabel,
        fieldDescription: FieldDescription,
        fieldError: FieldError,
        header: Header,
        headerTitle: HeaderTitle,
        headerDescription: HeaderDescription,
        headerImage: HeaderImage,
        goal: Goal,
        goalAchieved: GoalAchieved,
        receipt: DonationReceipt,
        donationSummaryItems: DonationSummaryItems,
        formError: FormError,
    },
};

export default defaultFormTemplates;
