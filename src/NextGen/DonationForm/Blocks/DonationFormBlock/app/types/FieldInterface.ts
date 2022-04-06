interface FieldInterface {
    type: string;
    name: string;
    label: string;
    readOnly: boolean;
    validationRules: {required: boolean};
    nodes?: FieldInterface[];
}

export default FieldInterface;
