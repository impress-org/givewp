import {useState} from '@wordpress/element';

/**
 * @since 3.0.0
 */
export default function usePopoverState() {
	const [isOpen, setIsOpen] = useState<boolean>(false);
	const open = () => setIsOpen(true);
	const close = () => setIsOpen(false);
	const toggle = () => setIsOpen((open) => !open);

	return {isOpen, setIsOpen, toggle, open, close};
}
