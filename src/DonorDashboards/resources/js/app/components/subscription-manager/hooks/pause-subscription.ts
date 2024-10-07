import {useState} from 'react';
import {managePausingSubscriptionWithAPI} from '../utils';

export type pauseDuration = number;
type id = string;

const usePauseSubscription = (id: id) => {
    const [loading, setLoading] = useState<boolean>(false);

    const handlePause = async (pauseDuration: pauseDuration) => {
        setLoading(true);
        try {
            await managePausingSubscriptionWithAPI({
                id,
                intervalInMonths: pauseDuration,
            });
        } catch (error) {
            console.error('Error pausing subscription:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleResume = async () => {
        setLoading(true);
        try {
            await managePausingSubscriptionWithAPI({
                id,
                action: 'resume',
            });
        } catch (error) {
            console.error('Error resuming subscription:', error);
        } finally {
            setLoading(false);
        }
    };

    return {
        loading,
        setLoading,
        handlePause,
        handleResume,
    };
};

export default usePauseSubscription;
