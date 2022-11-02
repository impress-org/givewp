import {Markup} from 'interweave';
import './interweave.scss';

const Interweave = ({column, item}) => {
    let value = item?.[column.name];
    if (value === undefined) {
        value = null;
    }

    return (
        <Markup
            tagName={'div'}
            attributes={{className: 'interweave'}}
            content={
                '<div class="typeBadge"> <div aria-labelledby="id" class="typeBadge__container typeBadge__container--single"><span class="typeBadge__badge">1X</span></div> <p id="badgeId" class="typeBadge__label">Recurring</p></div> '
            }
        />
    );
};
export default Interweave;

/*{
Donation Lists:

Status:
'<div class="statusBadge statusBadge--failed"> <p> Failed </p> </div>'
'<div class="statusBadge statusBadge--pending"> <p> Pending </p> </div>'
'<div class="statusBadge statusBadge--completed"> <p> Completed </p> </div>'
'<div class="statusBadge statusBadge--abandoned"> <p> Abandoned </p> </div>'

BadgeID:
'<div class="idBadge"> 27 </div>'

Payment Type:
'<div class="typeBadge"> <div aria-labelledby="id" class="typeBadge__container typeBadge__container--recurring"><span class="typeBadge__badge">recurring</span></div> <p id="badgeId" class="typeBadge__label">Recurring</p></div> '
'<div class="typeBadge"> <div aria-labelledby="id" class="typeBadge__container typeBadge__container--single"><span class="typeBadge__badge">1X</span></div> <p id="badgeId" class="typeBadge__label">Recurring</p></div> '

}*/
