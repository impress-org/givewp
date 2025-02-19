export type Attributes = {
    blockId: string;
    campaignId: number;
    title: string;
    commentLength: number;
    commentsPerPage: number;
    readMoreText: string;
    showAvatar: boolean;
    showDate: boolean;
    showName: boolean;
    showAnonymous: boolean;
};

export type CommentData = {
    comment: string;
    date: string;
    donorName: string;
    avatar: string;
};
