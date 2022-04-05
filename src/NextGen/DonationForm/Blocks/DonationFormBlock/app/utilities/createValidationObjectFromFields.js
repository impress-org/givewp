function createValidationObjectFromFields(fields) {
    return fields.reduce((rules, field) => {
        let rule = Joi;

        if (field.nodes) {
            field.nodes.map(({name, type, validationRules}) => {
                if (type === 'group') {
                    return null;
                }
                if (validationRules?.required) {
                    rule = rule.required();
                }

                if (field.type === 'email') {
                    rule = rule.email({tlds: false});
                }

                if (field.type === 'text') {
                    rule = rule.string();
                }

                rules[name] = rule;
            });
        } else {
            if (field.validationRules?.required) {
                rule = rule.required();
            }

            if (field.type === 'email') {
                rule = rule.email({tlds: false});
            }

            if (field.type === 'text') {
                rule = rule.string();
            }

            rules[field.name] = rule;
        }

        return rules;
    }, {});
}
