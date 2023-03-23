export type ButtonProps = {
    variant?: 'primary' | 'secondary' | 'danger';
    size?: 'small' | 'large';
    type?: 'button' | 'reset' | 'submit';
    children: React.ReactNode;

    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    disabled?: boolean;
    classname?: 'string';
};
