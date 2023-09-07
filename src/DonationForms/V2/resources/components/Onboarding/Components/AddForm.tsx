import {useState} from 'react';
import FormBuilderButtonPortal from './FormBuilderButtonPortal';

export default function AddForm() {
    const [showDialog, setShowDialog] = useState(false);

    return (
        <FormBuilderButtonPortal
            showDialog={showDialog}
            setShowDialog={setShowDialog}
        />
    )
}
