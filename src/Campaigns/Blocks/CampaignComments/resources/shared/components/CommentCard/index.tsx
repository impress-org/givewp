import {useState} from 'react';
import {CommentData} from '../CampaignComments';

import './styles.scss';
import {__} from '@wordpress/i18n';

export type AttributeProps = {
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

type CampaignCommentCardProps = {attributes: AttributeProps; data: CommentData};

export default function CampaignCommentCard({attributes, data}: CampaignCommentCardProps) {
    const [fullComment, setFullComment] = useState<boolean>(false);
    const {comment, date, donorName, avatar} = data;
    const {commentLength, readMoreText, showAvatar, showDate, showName} = attributes;

    const truncatedComment = comment.slice(0, commentLength) + (comment.length > commentLength ? '...' : '');

    return (
        <div className={'givewp-campaign-comment-block-card'}>
            {showAvatar && (
                <div className="givewp-campaign-comment-block-card__avatar">
                    <img src={avatar} alt={__('Donor avatar')} />
                </div>
            )}
            <div className={'givewp-campaign-comment-block__content'}>
                {showName && <p className={'givewp-campaign-comment-block-card__donor-name'}>{donorName}</p>}
                {showDate && <p className={'givewp-campaign-comment-block-card__details'}>{date}</p>}
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
