/**
 * Builds the expected registration options from the Field API validation rules
 *
 * @unreleased
 */
import {RegisterOptions} from "react-hook-form";

export default function buildRegisterValidationOptions(validationRules: {[key: string]: any}): RegisterOptions {
    return ['required', 'maxLength', 'minLength'].reduce((rules, rule) => {
        if (validationRules.hasOwnProperty(rule)) {
            rules[rule] = validationRules[rule];
        }

        return rules;
    }, {});
}
