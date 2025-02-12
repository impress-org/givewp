import {useState} from 'react';
import {commentData} from '../../window';
import './styles.scss';

export type AttributeProps = {
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

type CampaignCommentCardProps = {attributes: AttributeProps; data: commentData};

export default function CampaignCommentCard({attributes, data}: CampaignCommentCardProps) {
    const [fullComment, setFullComment] = useState<boolean>(false);
    const {comment, date, campaignTitle, donorName, avatar, anonymous} = data;
    const {commentLength, readMoreText, showAvatar, showDate, showName} = attributes;

    const truncatedComment = comment
        ? comment.slice(0, commentLength) + (comment.length > commentLength ? '...' : '')
        : '';

    return (
        <div className={'givewp-campaign-comment-block-card'}>
            {showAvatar && <div className="givewp-campaign-comment-block-card__avatar">{<img src={avatar} />}</div>}
            <div className={'givewp-campaign-comment-block__content'}>
                {showName && !!anonymous && (
                    <p className={'givewp-campaign-comment-block-card__donor-name'}>{donorName}</p>
                )}
                <p className={'givewp-campaign-comment-block-card__details'}>
                    {campaignTitle}
                    <span className={'givewp-campaign-comment-block-card__details__icon'} />
                    {showDate && date}
                </p>
                <p className={'givewp-campaign-comment-block-card__comment'}>
                    {fullComment ? comment : truncatedComment}
                </p>
                {comment?.length > commentLength && !fullComment && (
                    <button
                        className={'givewp-campaign-comment-block-card__read-more'}
                        onClick={() => setFullComment(!fullComment)}
                    >
                        {readMoreText}
                    </button>
                )}
            </div>
        </div>
    );
}
