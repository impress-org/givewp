export type Campaign = {
    id?: number;
    type: string;
    title: string;
    shortDescription: string;
    longDescription: string;
    logo: string;
    image: string;
    primaryColor: string;
    secondaryColor: string;
    goal: number;
    status: string;
    startDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    endDateTime: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    createdAt: string;
    /*updatedAt: string;*/
};
