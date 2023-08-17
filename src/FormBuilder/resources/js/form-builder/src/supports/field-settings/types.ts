export type FieldSettings = {
    label: FieldSettingProperty;
    metaKey: boolean;
    placeholder: FieldSettingProperty;
    description: FieldSettingProperty;
    required: FieldSettingProperty;
    storeAsDonorMeta: FieldSettingProperty;
    displayInAdmin: FieldSettingProperty;
    displayInReceipt: FieldSettingProperty;
    defaultValue: FieldSettingProperty;
    emailTag: FieldSettingProperty;
};

export type FieldSettingsSupport =
    | true
    | {
          label: FieldSettingProperty | boolean;
          metaKey: boolean;
          placeholder: FieldSettingProperty | boolean;
          description: FieldSettingProperty | boolean;
          required: FieldSettingProperty | boolean;
          storeAsDonorMeta: FieldSettingProperty | boolean;
          displayInAdmin: FieldSettingProperty | boolean;
          displayInReceipt: FieldSettingProperty | boolean;
          defaultValue: FieldSettingProperty | boolean;
          emailTag: FieldSettingProperty | boolean;
      };

export type FieldSettingProperty =
    | false
    | {
          default: any;
      };

export type FieldAttributes = {
    [key: string]: {
        type: string;
        default?: string | boolean;
        required?: boolean;
    };
};
