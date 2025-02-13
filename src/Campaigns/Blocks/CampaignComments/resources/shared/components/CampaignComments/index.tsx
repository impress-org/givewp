import {__} from '@wordpress/i18n';
import CampaignCommentCard, {AttributeProps} from '../CommentCard';
import getGiveCampaignCommentsBlockWindowData, {commentData} from '../../window';
import './styles.scss';

type CampaignCommentsProps = {
    attributes: AttributeProps;
};

export default function CampaignComments({attributes}: CampaignCommentsProps) {
    const comments = getGiveCampaignCommentsBlockWindowData();

    return (
        <div className={'givewp-campaign-comment-block'}>
            <h4 className={'givewp-campaign-comment-block__title'}>{attributes?.title}</h4>
            <p className={'givewp-campaign-comment-block__cta'}>
                {__('Leave a supportive message by donating to the campaign.', 'give')}
            </p>
            {comments && comments?.slice(0, attributes?.commentsPerPage)?.map((comment: commentData, index: number) => (
                <CampaignCommentCard key={`givewp-campaign-comment-${index}`} attributes={attributes} data={comment} />
            ))}
        </div>
    );
}
