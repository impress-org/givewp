import GiveIcon from "./give-icon";
import { ToolbarButton } from '@wordpress/components';
import {useState} from "react";

const Component = ({ saveCallback, toggleSecondarySidebar, toggleShowSidebar }) => {

    const [ isSaving, setSaving ] = useState(false)

    const onSave = () => {
        setSaving(true)
        saveCallback().finally(() => {
            setSaving(false)
        })
    }

    return (
        <header style={{height: '60px', display: 'flex', justifyContent: 'space-between', alignItems: 'center'}}>
            <section style={{display: 'flex', gap: '20px', alignItems: 'center'}}>
                <div style={{ height: '60px', width: '60px', backgroundColor: '#66bb6a', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                    <div style={{ marginLeft: '-7px' }}>
                        <GiveIcon />
                    </div>
                </div>
                <ToolbarButton onClick={toggleSecondarySidebar}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd"
                              d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                              clipRule="evenodd"/>
                    </svg>
                </ToolbarButton>
            </section>
            <section style={{ marginRight: '20px', display: 'flex', gap: '10px', alignItems: 'center'}}>
                <ToolbarButton onClick={toggleShowSidebar}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd"
                              d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                              clipRule="evenodd"/>
                    </svg>
                </ToolbarButton>
                <ToolbarButton onClick={onSave} disabled={isSaving}>
                    { !! isSaving && <div>Saving...</div>}
                    { ! isSaving && (<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"/>
                    </svg>)}
                </ToolbarButton>
            </section>
        </header>
    )
}

export default Component
