import {compose} from "@wordpress/compose";
import withButtons from "./filters/withButtons";
import withText from "./filters/withText";
import withDefaults from "./filters/withDefaults";
import designStepsList from "./design-steps";
import schemaStepsList from "./schema-steps";

const composeSteps = (steps) => {
    return Object.values(compose(
        withText,
        withButtons,
        withDefaults,
    )(steps))
}

export const designSteps =  composeSteps(designStepsList)

export const schemaSteps = composeSteps(schemaStepsList)

