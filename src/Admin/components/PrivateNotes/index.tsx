import {__} from '@wordpress/i18n';
import useSWR from 'swr';
import {Dispatch, SetStateAction, useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {useDispatch} from '@wordpress/data';
import {ConfirmationDialogIcon, DeleteIcon, DotsMenuIcon, EditIcon, NotesIcon} from './Icons';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';
import Spinner from '../Spinner';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import style from './style.module.scss';
import cx from 'classnames';
import {formatTimestamp} from '@givewp/src/Admin/utils';

/**
 * @unreleased
 */
export default function PrivateNotes({donorId, context}: {
    donorId: number,
    context: [boolean, Dispatch<SetStateAction<boolean>>]
}) {
    const endpoint = `/givewp/v3/donors/${donorId}/notes`;
    const [state, setNoteState] = useState({
        isSavingNote: false,
        note: '',
    });

    const [isAddingNote, setIsAddingNote] = context;

    const dispatch = useDispatch('givewp/admin-details-page-notifications');

    const {
        data,
        isLoading,
        isValidating,
        mutate,
    } = useSWR<[]>(endpoint, (url) => apiFetch({path: url}), {revalidateOnFocus: false});

    const saveNote = () => {
        setState({isSavingNote: true});
        apiFetch({path: endpoint, method: 'POST', data: {content: state.note}})
            .then((response) => {
                mutate(response).then(() => {
                    setIsAddingNote(false);
                    dispatch.addSnackbarNotice({
                        id: 'add-note',
                        content: __('You added a private note', 'give'),
                    });
                });
            });
    };

    const deleteNote = (id: number) => {
        apiFetch({path: `/givewp/v3/donors/${donorId}/notes/${id}`, method: 'DELETE', data: {id}})
            .then(async (response) => {
                await mutate(response);
                dispatch.addSnackbarNotice({
                    id: 'delete-note',
                    content: __('Private note deleted successfully', 'give'),
                });
            });
    };

    const editNote = (id: number, content: string) => {
        apiFetch({path: `/givewp/v3/donors/${donorId}/notes/${id}`, method: 'POST', data: {id, content}})
            .then(async (response) => {
                await mutate(response);
                dispatch.addSnackbarNotice({
                    id: 'delete-note',
                    content: __('Private note edited', 'give'),
                });
            });
    };

    const setState = (props) => {
        setNoteState((prevState) => {
            return {
                ...prevState,
                ...props,
            };
        });
    };

    if (isLoading || isValidating) {
        return (
            <div style={{margin: '0 auto'}}>
                <Spinner />
            </div>
        );
    }

    return (
        <div className={style.notesContainer}>
            {isAddingNote && (
                <div className={style.addNoteContainer}>
                    <textarea
                        className={style.textarea}
                        onChange={(e) => setState({note: e.target.value})}
                    ></textarea>

                    <div className={style.textAreaButtons}>
                        <button
                            className={cx(style.button, style.cancelBtn)}
                            onClick={() => setIsAddingNote(false)}
                        >
                            {__('Cancel', 'give')}
                        </button>
                        <button
                            className={cx(style.button, style.saveBtn)}
                            onClick={(e) => {
                                e.preventDefault();
                                saveNote();
                            }}
                        >
                            {__('Save', 'give')}
                        </button>
                    </div>
                </div>
            )}
            {data.length ? (
                <>
                    {data.map((note) => {
                        return (
                            <Note
                                note={note}
                                onDelete={(id: number) => deleteNote(id)}
                                onEdit={(id: number, content: string) => editNote(id, content)}
                            />
                        );
                    })}
                </>
            ) : (
                <>
                    {!isAddingNote && (
                        <div style={{margin: '0 auto'}}>
                            <NotesIcon />
                            <p>{__('No notes yet', 'give')}</p>
                        </div>
                    )}
                </>
            )}
        </div>
    );
}


/**
 * @unreleased
 */
const Note = ({note, onDelete, onEdit}) => {
    const [showMenuIcon, setShowMenuIcon] = useState(false);
    const [showContextMenu, setShowContextMenu] = useState(false);
    const [currentlyEditing, setCurrentlyEditing] = useState(null);
    const [content, setContent] = useState('');
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);

    return (
        <>
            <div
                className={style.noteContainer}
                onMouseEnter={() => setShowMenuIcon(true)}
                onMouseLeave={() => {
                    setShowMenuIcon(false);
                    setShowContextMenu(false);
                }}
            >
                {currentlyEditing ? (
                    <>
                        <div className={style.addNoteContainer}>
                        <textarea
                            className={style.textarea}
                            onChange={(e) => setContent(e.target.value)}
                        >
                            {note.content}
                        </textarea>

                            <div className={style.textAreaButtons}>
                                <button
                                    className={cx(style.button, style.cancelBtn)}
                                    onClick={() => {
                                        setCurrentlyEditing(null);
                                        setShowContextMenu(false);
                                    }}
                                >
                                    {__('Cancel', 'give')}
                                </button>
                                <button
                                    className={cx(style.button, style.saveBtn)}
                                    onClick={(e) => {
                                        e.preventDefault();
                                        setShowContextMenu(false);
                                        onEdit(note.id, content);
                                    }}
                                >
                                    {__('Save', 'give')}
                                </button>
                            </div>
                        </div>
                    </>
                ) : (
                    <>
                        <div className={style.note}>
                            <div className={style.title}>
                                {note.content}
                            </div>

                            {showMenuIcon && (
                                <div
                                    className={style.dotsMenu}
                                    onClick={() => setShowContextMenu(true)}
                                >
                                    <DotsMenuIcon />
                                    {showContextMenu && (
                                        <div className={style.menu}>
                                            <a
                                                href="#"
                                                className={style.menuItem}
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    setShowContextMenu(false);
                                                    setCurrentlyEditing(note.id);
                                                }}
                                            >
                                                <EditIcon /> {__('Edit', 'give')}
                                            </a>
                                            <a
                                                href="#"
                                                className={cx(style.menuItem, style.delete)}
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    setShowContextMenu(false);
                                                    setShowDeleteDialog(true);
                                                }}
                                            >
                                                <DeleteIcon /> {__('Delete', 'give')}
                                            </a>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                        <div className={style.date}>
                            {formatTimestamp(note.createdAt.date)}
                        </div>
                    </>
                )}
                <ConfirmationDialog
                    title={__('Delete Note', 'give')}
                    isOpen={showDeleteDialog}
                    handleClose={() => setShowDeleteDialog(false)}
                    handleConfirm={() => {
                        onDelete(note.id);
                    }}
                />
            </div>
            <NotificationPlaceholder type="snackbar" />
        </>
    );
};


function ConfirmationDialog({
    isOpen,
    title,
    handleClose,
    handleConfirm
}: {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
}) {
    return (
        <ModalDialog
            icon={<ConfirmationDialogIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
        >
            <>
                <div className={style.dialogContent}>
                    {__('Are you sure you want to delete this note?', 'give')}
                </div>
                <div className={style.dialogButtons}>
                    <button
                        className={style.cancelButton}
                        onClick={handleClose}
                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={style.confirmButton}
                        onClick={handleConfirm}
                    >
                        {__('Delete note', 'give')}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}

