import {compose} from "@wordpress/compose";
import withButtons from "./filters/withButtons";
import withText from "./filters/withText";
import withDefaults from "./filters/withDefaults";
import designStepsList from "./design-steps";
import schemaStepsList from "./schema-steps";

export const designSteps =  Object.values(compose(
    withText,
    withButtons,
    withDefaults,
)(designStepsList))

export const schemaSteps = Object.values(compose(
    withText,
    withButtons,
    withDefaults,
)(schemaStepsList))

