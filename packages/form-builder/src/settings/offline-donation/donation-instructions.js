import {useState} from "react";
import {useToggleState} from "../../hooks";
import {Button} from "@wordpress/components";
import Popout, { PopoutContainer } from "../../components/sidebar/popout";
import {RichText} from "@wordpress/block-editor/build/components";

const DonationInstructions = () => {

    const { state: showPopout, toggle: toggleShowPopout } = useToggleState()

    const [ content, setContent ] = useState(`
            <p>You can customize instructions in the form settings.</p>
            <p>Please make checks payable to <strong>"{sitename}"</strong>.</p>
            <p>Your donation is greatly appreciated!</p>
        `)

    return (
        <>
            <div style={{ marginTop: '10px', width: '100%', display: "flex", justifyContent: "space-between", alignItems: "center"}}>
                Donation Instructions
                <Button onClick={toggleShowPopout} style={{color:'white',backgroundColor:'#68BF6B'}}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round"
                              d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </Button>
            </div>
            { showPopout && (
                <Popout>
                    <PopoutContainer>
                        <div style={{width: '400px'}}>
                            <header style={{padding:'15px 10px', borderBottom: '1px solid lightgray'}}>
                                <h3 style={{margin: '0'}}>Donation Instructions</h3>
                            </header>
                            <div style={{padding:'10px', borderBottom: '1px solid lightgray'}}>
                                <RichText
                                    style={{height: '200px',maxHeight: '200px',overflowY:'scroll',lineHeight:'1.6'}}
                                    multiline={true}
                                    identifier="content"
                                    tagName="p"
                                    value={ content }
                                    onChange={ setContent }
                                    placeholder={'PLACEHOLDER TEXT'}
                                />
                            </div>
                            <div style={{display:'flex',gap:'20px',padding:'10px', borderBottom: '1px solid lightgray'}}>
                                <span>Visual</span>
                                <span>Text</span>
                            </div>
                            <div style={{display:'flex',flexDirection:'column',gap:'20px',padding:'15px 10px', borderBottom: '1px solid lightgray'}}>
                                <div>Text Style</div>
                                <div>Text Format</div>
                                <div>Alignment</div>
                                <div>List Style</div>
                                <div>Insert</div>
                            </div>
                        </div>
                    </PopoutContainer>
                </Popout>
            )}</>
    )
}

export default DonationInstructions
