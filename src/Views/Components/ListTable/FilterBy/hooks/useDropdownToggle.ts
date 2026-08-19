import { useState, useRef, useEffect } from 'react';

/**
 * Custom hook to handle dropdown open/close logic
 *
 * @since 4.12.0
 */
export function useDropdownToggle() {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
            return () => document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [isOpen]);

    return { isOpen, setIsOpen, dropdownRef };
}
