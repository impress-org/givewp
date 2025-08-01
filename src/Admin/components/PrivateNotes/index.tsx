import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';
import useSWR from 'swr';
import React, {useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {useDispatch} from '@wordpress/data';
import {ConfirmationDialogIcon, DeleteIcon, DotsMenuIcon, EditIcon, NotesIcon} from './Icons';
import Spinner from '../Spinner';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import style from './style.module.scss';
import cx from 'classnames';
import {formatTimestamp} from '@givewp/src/Admin/utils';
import Header from '@givewp/src/Admin/components/Header';

/**
 * @since 4.5.0
 */
type DonorNote = {
    id: number;
    donorId: number;
    content: string;
    createdAt: {
        date: string;
    };
}

/**
 * @since 4.5.0
 */
type NoteState = {
    isAddingNote: boolean;
    isSavingNote: boolean;
    note: string;
    perPage: number;
}

/**
 * @since 4.6.0
 */
export function DonorNotes({donorId}: {donorId: number}) {
    return <PrivateNotes endpoint={`/givewp/v3/donors/${donorId}/notes`} />
}

/**
 * @since 4.6.0
 */
export function DonationNotes({donationId}: {donationId: number}) {
    return <PrivateNotes endpoint={`/givewp/v3/donations/${donationId}/notes`} />
}

/**
 * @since 4.4.0
 */
function PrivateNotes({endpoint}: {endpoint: string}) {
    const [state, setNoteState] = useState<NoteState>({
        isAddingNote: false,
        isSavingNote: false,
        note: '',
        perPage: 5,
    });

    const dispatch = useDispatch('givewp/admin-details-page-notifications');

    const {
        data,
        isLoading,
        isValidating,
        mutate,
    } = useSWR<{data: DonorNote[]; totalPages: number; totalItems: number}>(endpoint, async (url) => {
        const response = await apiFetch({
            path: addQueryArgs(url, {page: 1, per_page: state.perPage}),
            parse: false,
        }) as Response;
        const data = await response.json();
        return {
            data,
            totalPages: Number(response.headers.get('X-WP-TotalPages')),
            totalItems: Number(response.headers.get('X-WP-Total')),
        };
    }, {revalidateOnFocus: false});

    const saveNote = () => {
        setState({isSavingNote: true});
        apiFetch({path: endpoint, method: 'POST', data: {content: state.note}})
            .then((response) => {
                mutate(response).then(() => {
                    setState({isAddingNote: false})
                    dispatch.addSnackbarNotice({
                        id: 'add-note',
                        content: __('You added a private note', 'give'),
                    });
                });
            });
    };

    const deleteNote = (id: number) => {
        apiFetch({path: `${endpoint}/${id}`, method: 'DELETE', data: {id}})
            .then(async (response) => {
                await mutate(response);
                dispatch.addSnackbarNotice({
                    id: 'delete-note',
                    content: __('Private note deleted successfully', 'give'),
                });
            });
    };

    const editNote = (id: number, content: string) => {
        apiFetch({path: `${endpoint}/${id}`, method: 'PATCH', data: {content}})
            .then(async (response) => {
                await mutate(response);
                dispatch.addSnackbarNotice({
                    id: 'edit-note',
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
        <>
            <Header
                title={__('Private Note', 'give')}
                subtitle={__('This note will be seen by only admins', 'give')}
                actionOnClick={() => setState({isAddingNote: true})}
                actionText={__('Add note', 'give')}
            />
            <div className={style.notesContainer}>
                {state.isAddingNote && (
                    <div className={style.addNoteContainer}>
                    <textarea
                        className={style.textarea}
                        onChange={(e) => setState({note: e.target.value})}
                    ></textarea>

                        <div className={style.textAreaButtons}>
                            <button
                                className={cx(style.button, style.cancelBtn)}
                                onClick={() => setState({isAddingNote: false})}
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
                {data?.data?.length ? (
                    <>
                        {data.data.map((note) => {
                            return (
                                <Note
                                    key={note.id}
                                    note={note}
                                    onDelete={(id: number) => deleteNote(id)}
                                    onEdit={(id: number, content: string) => editNote(id, content)}
                                />
                            );
                        })}
                    </>
                ) : (
                    <>
                        {!state.isAddingNote && (
                            <div style={{margin: '0 auto', textAlign: 'center'}}>
                                <NotesIcon />
                                <p className={style.noNotesText}>{__('No notes yet', 'give')}</p>
                            </div>
                        )}
                    </>
                )}

                <div className={style.showMoreContainer}>
                    {data?.data?.length > 0 && data.totalItems > state.perPage && (
                        <button
                            className={style.showMoreButton}
                            onClick={async (e) => {
                                e.preventDefault();
                                setNoteState((prevState) => {
                                    return {
                                        ...prevState,
                                        perPage: prevState.perPage += 5,
                                    };
                                });

                                await mutate(endpoint);
                            }}>
                            {__('Show more', 'give')}
                        </button>
                    )}
                </div>
            </div>
        </>
    );
}


/**
 * @since 4.4.0
 */
const Note = ({note, onDelete, onEdit}) => {
    const [showContextMenu, setShowContextMenu] = useState(false);
    const [currentlyEditing, setCurrentlyEditing] = useState(null);
    const [content, setContent] = useState(note.content);
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);

    return (
        <>
            <div
                onMouseLeave={() => {
                    setShowContextMenu(false);
                }}
            >
                {currentlyEditing ? (
                    <>
                        <div className={style.addNoteContainer}>
                            <textarea
                                className={style.textarea}
                                onChange={(e) => setContent(e.target.value)}
                                value={content}
                            ></textarea>

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
                        <div className={style.noteContainer}>
                            <div className={style.note}>
                                <div className={style.title}>
                                    {note.content}
                                </div>

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
                            </div>
                            <div className={style.date}>
                                {formatTimestamp(note.createdAt.date)}
                            </div>
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
        </>
    );
};


/**
 * @since 4.5.0
 */
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

