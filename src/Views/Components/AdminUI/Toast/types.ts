export type ToastProps = {
    resultType: 'success' | 'error' | null
    resultMessage: string
    closeMessage:() => void
    showMessage: boolean
};
