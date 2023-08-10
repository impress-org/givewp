import {useState} from '@wordpress/element';

/**
 * @0.6.0
 */
export default function usePopoverState() {
	const [isOpen, setIsOpen] = useState<boolean>(false);
	const open = () => setIsOpen(true);
	const close = () => setIsOpen(false);
	const toggle = () => setIsOpen(!isOpen);

	return {isOpen, setIsOpen, toggle, open, close};
}