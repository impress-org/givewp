import TextField from './fields/Text';
import TextAreaField from './fields/TextArea';
import EmailField from './fields/Email';
import HiddenField from './fields/Hidden';
import CheckboxField from './fields/Checkbox';
import RadioField from './fields/Radio';
import HtmlElement from './elements/Html';
import DonationSummaryElement from './elements/DonationSummary';
import NameGroup from './groups/Name';
import DonationAmountGroup from './groups/DonationAmount';
import SectionLayout from './layouts/Section';
import Form from './layouts/Form';
import AmountField from './fields/Amount';
import SelectField from './fields/Select';
import Gateways from './fields/Gateways';
import Paragraph from './elements/Paragraph';
import FieldLabel from './layouts/FieldLabel';
import FieldError from './layouts/FieldError';
import Header from './layouts/Header';
import HeaderTitle from './layouts/HeaderTitle';
import HeaderDescription from './layouts/HeaderDescription';
import Goal from './layouts/Goal';
import GoalAchieved from './layouts/GoalAchieved';
import NodeWrapper from './layouts/NodeWrapper';
import DonationReceipt from './layouts/DonationReceipt';
import MultiStepForm from './layouts/MultiStepForm';

const defaultFormTemplates = {
    fields: {
        amount: AmountField,
        text: TextField,
        textarea: TextAreaField,
        email: EmailField,
        hidden: HiddenField,
        gateways: Gateways,
        select: SelectField,
        checkbox: CheckboxField,
        radio: RadioField,
    },
    elements: {
        paragraph: Paragraph,
        html: HtmlElement,
        donationSummary: DonationSummaryElement,
    },
    groups: {
        name: NameGroup,
        donationAmount: DonationAmountGroup,
    },
    layouts: {
        wrapper: NodeWrapper,
        section: SectionLayout,
        form: Form,
        multiStepForm: MultiStepForm,
        fieldLabel: FieldLabel,
        fieldError: FieldError,
        header: Header,
        headerTitle: HeaderTitle,
        headerDescription: HeaderDescription,
        goal: Goal,
        goalAchieved: GoalAchieved,
        receipt: DonationReceipt,
    },
};

export default defaultFormTemplates;
