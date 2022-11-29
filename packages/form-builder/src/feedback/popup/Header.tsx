import {Button, Icon} from "@wordpress/components";
import CloseIcon from "./CloseIcon";
import HeaderIcon from "./HeaderIcon";

const Header = ({title, closeCallback}) => {
    return (
        <header style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
        }}>
            <div style={{
                display: 'flex',
                alignItems: 'center',
                gap: '10px',
                fontSize: '16px',
                fontWeight: 500,
                lineHeight: '16px',
            }}>
                <Icon icon={HeaderIcon} />
                {title}
            </div>
            <Button
                style={{padding: 0, height: 'auto'}}
                icon={CloseIcon}
                onClick={closeCallback}
            />
        </header>
    )
}

export default Header;
