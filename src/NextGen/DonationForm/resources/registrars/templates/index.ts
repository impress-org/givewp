import TextField from './fields/Text';
import TextAreaField from './fields/TextArea';
import EmailField from './fields/Email';
import HiddenField from './fields/Hidden';
import HtmlElement from './elements/Html';
import DonationSummaryElement from './elements/DonationSummary';
import NameGroup from './groups/Name';
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
import NodeWrapper from './layouts/NodeWrapper';

const defaultFormTemplates = {
    fields: {
        amount: AmountField,
        text: TextField,
        textarea: TextAreaField,
        email: EmailField,
        hidden: HiddenField,
        gateways: Gateways,
        select: SelectField,
    },
    elements: {
        paragraph: Paragraph,
        html: HtmlElement,
        donationSummary: DonationSummaryElement,
    },
    groups: {
        name: NameGroup,
    },
    layouts: {
        wrapper: NodeWrapper,
        section: SectionLayout,
        form: Form,
        fieldLabel: FieldLabel,
        fieldError: FieldError,
        header: Header,
        headerTitle: HeaderTitle,
        headerDescription: HeaderDescription,
        goal: Goal,
    },
};

export default defaultFormTemplates;
