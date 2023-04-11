import {useRef, useState} from 'react';

import {__} from '@wordpress/i18n';
import {format} from 'date-fns';

import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import SectionHeader, {HeaderAction, Title} from '@givewp/components/AdminUI/SectionHeader';
import Button from '@givewp/components/AdminUI/Button';
import {RadioButtonField, TextAreaField} from '@givewp/components/AdminUI/FormElements';
import {FieldsetContainer} from '@givewp/components/AdminUI/ContainerLayout';
import EmptyState from '@givewp/components/AdminUI/EmptyState';

import styles from './style.module.scss';
import AddIcon from '@givewp/components/AdminUI/Icons/AddIcon';
import cx from 'classNames';
import TrashIcon from '@givewp/components/AdminUI/Icons/TrashIcon';
import PenToPaperIcon from '@givewp/components/AdminUI/Icons/PenToPaperIcon';
import {useDeleteRequest, usePatchRequest, usePostRequest} from '@givewp/components/AdminUI/api';

const notes = [
    {
        id: 1,
        donationId: 1,
        donorAvatar:
            'https://images.unsplash.com/photo-1575936123452-b67c3203c357?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8aW1hZ2V8ZW58MHx8MHx8&w=1000&q=80',
        content: 'this is a long test comment',
        type: 'private',
        createdAt: new Date(),
    },
    {
        id: 2,
        donorAvatar:
            'https://images.unsplash.com/photo-1575936123452-b67c3203c357?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8aW1hZ2V8ZW58MHx8MHx8&w=1000&q=80',
        content: 'this is a long test comment',
        type: 'private',
        createdAt: new Date(),
    },
    {
        id: 3,
        donorAvatar:
            'https://images.unsplash.com/photo-1575936123452-b67c3203c357?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8aW1hZ2V8ZW58MHx8MHx8&w=1000&q=80',
        content:
            'Sutler measured fer yer chains lookout warp dead men tell no tales Nelsons folly scourge of the seven seas fore me smartly. Gangplank grog blossom prow Admiral of the Black blow carouser to go on.',
        type: 'public',
        createdAt: new Date(),
    },
];

const {id: donationId} = window.GiveDonations.donationDetails;
const {apiNonce} = window.GiveDonations;

export type NotesContentProps = {id: number; donorAvatar: string; content: string; type: string; createdAt: Date};

export function DonorNotes() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
    const [noteId, setNoteId] = useState<number>();
    const [notesList, setNotesList] = useState<Array<NotesContentProps>>(notes);

    const endpoint = `${donationId}/notes`;
    const {postData} = usePostRequest(endpoint, apiNonce, '', '');
    const {deleteData} = useDeleteRequest(endpoint, apiNonce);
    const {patchData} = usePatchRequest(endpoint, apiNonce);

    const openModal = () => {
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setNoteId(null);
        setIsModalOpen(false);
    };

    const handleCreateNote = (response, data) => {
        const {content, type} = data;
        const createdNote = {
            id: response?.id ?? 10000,
            donationId: donationId,
            donorAvatar: null,
            content: content,
            type: type,
            createdAt: new Date(),
        };

        setNotesList([createdNote, ...notesList]);
    };

    const handleEditNote = (response, data) => {
        const updatedNotesList = notesList.map((note) => {
            if (note.id === noteId) {
                return {
                    ...note,
                    content: data.content,
                    type: data.type,
                };
            }
            return note;
        });

        setNotesList(updatedNotesList);
    };

    const handleApiRequest = async (data) => {
        const requestMethod = noteId ? patchData : postData;

        try {
            const response = await requestMethod(data);
            requestMethod === postData ? handleCreateNote(response, data) : handleEditNote(response, data);
        } catch (error) {
            console.error(error);
        }
        closeModal();
    };

    const handleDeleteRequest = async (id) => {
        try {
            await deleteData(id);
            setNotesList(notesList.filter((note) => note.id !== id));
        } catch (error) {
            console.error(error);
        }
        setNoteId(id);
    };

    const onEditNote = (id) => {
        setIsModalOpen(true);
        setNoteId(id);
    };

    return (
        <section>
            <SectionHeader>
                <Title title={__('Donor notes', 'give')} />
                <HeaderAction action={openModal}>
                    <AddIcon />
                    {__('Add note', 'give')}
                </HeaderAction>
            </SectionHeader>
            <SectionContainer list={notesList} onEditNote={onEditNote} deleteNote={handleDeleteRequest} />
            <ModalDialog
                open={isModalOpen}
                onClose={closeModal}
                handleClose={closeModal}
                title={__('Donor note', 'give')}
            >
                <NotesDialog handleApiRequest={handleApiRequest} noteId={noteId} />
            </ModalDialog>
        </section>
    );
}

function SectionContainer({list, onEditNote, deleteNote}) {
    return (
        <FieldsetContainer>
            <div className={styles.notesContainer}>
                {list.length ? (
                    list.map(({id: noteId, donorAvatar, content, createdAt, type}) => (
                        <div key={noteId} className={styles.notes}>
                            <div className={styles.wrapper}>
                                <img src={donorAvatar} alt={'profile image'} />
                                <div className={styles.container}>
                                    <span>
                                        {format(createdAt, "do MMMM yyyy ' . ' hh:mma")}
                                        <span className={cx(styles.noteType, styles[type])}>{type}</span>
                                    </span>
                                    <span className={styles.content}>{content}</span>
                                </div>
                            </div>

                            <span className={styles.icons}>
                                <span className={styles.delete} role={'button'} onClick={(id) => deleteNote(noteId)}>
                                    <TrashIcon />
                                </span>
                                <span className={styles.edit} role={'button'} onClick={(id) => onEditNote(noteId)}>
                                    <PenToPaperIcon />
                                </span>
                            </span>
                        </div>
                    ))
                ) : (
                    <EmptyState message={__('No notes', 'give')} />
                )}
            </div>
        </FieldsetContainer>
    );
}

export type NotesDialogProps = {
    handleApiRequest: (ref) => void;
    noteId: number;
};

function NotesDialog({handleApiRequest, noteId}: NotesDialogProps) {
    const noteRef = useRef(null);
    const [type, setType] = useState(getNoteById(noteId, notes)?.type ?? 'private');

    const defaultContent = getNoteById(noteId, notes)?.content ?? '';

    const handleTypeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setType(event.target.defaultValue);
    };

    return (
        <div>
            <TextAreaField
                defaultValue={defaultContent}
                ref={noteRef}
                name={'notes'}
                placeholder={__('Type in your note', 'give')}
                label={__('Notes', 'give')}
                type={'text'}
            />
            <div className={styles.radioButtonContainer}>
                <span>{__('Note type', 'give')}</span>
                <RadioButtonField
                    name={'noteType'}
                    defaultValue={'private'}
                    checked={type === 'private'}
                    label={__('Private note', 'give')}
                    type={'radio'}
                    onChange={handleTypeChange}
                />
                <RadioButtonField
                    name={'noteType'}
                    defaultValue={'public'}
                    checked={type === 'public'}
                    label={__('Note to donor', 'give')}
                    type={'radio'}
                    onChange={handleTypeChange}
                />
            </div>
            <Button
                variant={'primary'}
                size={'large'}
                onClick={() =>
                    handleApiRequest({
                        content: noteRef.current?.value,
                        type: type,
                    })
                }
                disabled={false}
            >
                {__('Save note', 'give')}
            </Button>
        </div>
    );
}

function getNoteById(id, array) {
    return array.find((item) => item.id === id);
}
