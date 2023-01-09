import useDebounce from '@givewp/components/ListTable/hooks/useDebounce';

export default function useDebouncedEventHandler(eventHandler) {
    const debouncedCallback = useDebounce(eventHandler);
    return (event) => {
        event.persist();
        debouncedCallback(event);
    };
}
