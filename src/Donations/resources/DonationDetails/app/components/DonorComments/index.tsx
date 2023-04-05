import {Fragment, useRef, useState} from 'react';

import {__} from '@wordpress/i18n';

import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import SectionHeader, {HeaderAction, Title} from '@givewp/components/AdminUI/SectionHeader';
import Button from '@givewp/components/AdminUI/Button';
import {TextAreaField} from '@givewp/components/AdminUI/FormElements';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';
import EmptyState from '@givewp/components/AdminUI/EmptyState';

import styles from './style.module.scss';
import {apiNonce, apiRoot} from '../../../../window';
import {usePostRequest} from '@givewp/components/AdminUI/api';
import PenToPaperIcon from '@givewp/components/AdminUI/Icons/PenToPaperIcon';
import AddIcon from '@givewp/components/AdminUI/Icons/AddIcon';

/**
 *
 * @unreleased
 */
const {comment, donorAvatar, id} = window.GiveDonations.donationDetails;

export default function DonorComments() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const openModal = () => {
        setIsModalOpen(true);
    };

    const hasComment = !!comment;

    const endpoint = `${apiRoot}/${id}`;
    const successMessage = __('Donation details have been updated successfully', 'give');
    const errorMessage = __('There was an error while updating your comment. Please try again.', 'give');

    const {postData} = usePostRequest(endpoint, apiNonce, successMessage, errorMessage);

    const handlePostRequest = async (updatedComment) => {
        const data = {comment: updatedComment};

        try {
            await postData(data);
        } catch (error) {
            console.error(error);
        }
        setIsModalOpen(false);
    };

    return (
        <section>
            <SectionHeader>
                <Title title={__('Donor comment', 'give')} />
                <HeaderAction action={openModal}>
                    {hasComment ? (
                        <Fragment>
                            <PenToPaperIcon />
                            {__('Edit comment', 'give')}
                        </Fragment>
                    ) : (
                        <Fragment>
                            <AddIcon />
                            {__('Add comment', 'give')}
                        </Fragment>
                    )}
                </HeaderAction>
            </SectionHeader>

            <SectionContainer hasComment={hasComment} />

            <ModalDialog
                open={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                handleClose={() => setIsModalOpen(false)}
                title={__('Donor comment', 'give')}
            >
                <CommentDialog handlePostRequest={handlePostRequest} />
            </ModalDialog>
        </section>
    );
}

/**
 *
 * @unreleased
 */
function SectionContainer({hasComment}: {hasComment: boolean}) {
    return (
        <FieldsetContainer>
            {hasComment ? (
                <div className={styles.comments}>
                    <img src={donorAvatar} alt={'profile image'} /> <span>{comment}</span>
                </div>
            ) : (
                <EmptyState message={__('No comment', 'give')} />
            )}
        </FieldsetContainer>
    );
}

/**
 *
 * @unreleased
 */
function CommentDialog({handlePostRequest}) {
    const commentRef = useRef(null);

    return (
        <div>
            <TextAreaField
                defaultValue={comment}
                ref={commentRef}
                name={'comments'}
                placeholder={__('Type in your comment', 'give')}
                label={__('Comment', 'give')}
                type={'text'}
            />
            <Button
                variant={'primary'}
                size={'large'}
                onClick={() => handlePostRequest(commentRef.current.value)}
                disabled={false}
            >
                {__('Save changes', 'give')}
            </Button>
        </div>
    );
}
