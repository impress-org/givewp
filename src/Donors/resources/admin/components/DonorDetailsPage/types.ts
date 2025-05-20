import { FC } from "react";

export type DonorDetailsTab = {
    id: string;
    title: string;
    content: FC;
    fullwidth?: boolean;
};
