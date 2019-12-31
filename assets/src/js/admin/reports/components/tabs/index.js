import Tab from '../tab'

const Tabs = (props) => {
    return (
        <div className='nav-tab-wrapper give-nav-tab-wrapper'>
            <Tab to='/'>
                Overview
            </Tab>
            <a className='nav-tab' href={giveReportsData.legacyReportsUrl}>Legacy Reports Page</a>
        </div>
    )
}
export default Tabs