import {FieldErrors} from "react-hook-form";

export default function getErrorByFieldName(errors: FieldErrors, name: string){
    return errors.hasOwnProperty(name) ? errors[name].message : null;
}

