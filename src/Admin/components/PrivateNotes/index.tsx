import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';
import useSWR from 'swr';
import {useState} from 'react';
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
 * @since 4.8.0 Include loadingId in the state to manage loading states per note.
 * @since 4.5.0
 */
type NoteState = {
    notes: DonorNote[];
    loadingId: number | null;
    totalItems: number;
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
 * @since 4.8.0
 */
export function SubscriptionNotes({subscriptionId}: {subscriptionId: number}) {
    return <PrivateNotes endpoint={`/givewp/v3/subscriptions/${subscriptionId}/notes`} />
}

/**
 * @since 4.8.0 Manage local state to handle loading indicators per note.
 * @since 4.4.0
 */
function PrivateNotes({endpoint}: {endpoint: string}) {
    const [state, setNoteState] = useState<NoteState>({
        notes: [],
        loadingId: undefined,
        totalItems: 0,
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

        setState({
            notes: data,
            totalItems: Number(response.headers.get('X-WP-Total')),
        });

        return {
            data,
            totalPages: Number(response.headers.get('X-WP-TotalPages')),
            totalItems: Number(response.headers.get('X-WP-Total')),
        };
    }, {revalidateOnFocus: false});

    const initialLoad = (isLoading || isValidating) && state.loadingId === undefined;

    const saveNote = () => {
        const tempId = Date.now();
        const tempNote = {
            id: tempId,
            content: state.note,
            createdAt: {date: new Date().toISOString()}
        };

        // Add temporary note to the UI state.
        setState({
            loadingId: tempId,
            notes: [tempNote, ...state.notes],
            isAddingNote: false,
            isSavingNote: true
        });

        apiFetch({path: endpoint, method: 'POST', data: {content: state.note}})
            .then(async (response) => {
                await mutate(response);
                setState({
                    isAddingNote: false,
                    isSavingNote: false,
                });
                dispatch.addSnackbarNotice({
                    id: 'add-note',
                    content: __('You added a private note', 'give'),
                });
            });
    };

    const deleteNote = (id: number) => {
        setState({loadingId: id});
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
        setState({loadingId: id});
        apiFetch({path: `${endpoint}/${id}`, method: 'PATCH', data: {content}})
            .then(async (response) => {
                await mutate(response);
                setState({
                    loadingId: null,
                    notes: state.notes.map((note) => note.id === id ? response : note)
                });
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

    return (
        <>
            <Header
                title={__('Private Note', 'give')}
                subtitle={__('This note will be seen by only admins', 'give')}
                actionOnClick={() => setState({isAddingNote: true})}
                actionText={__('Add note', 'give')}
            />
            {initialLoad && (
                <div style={{margin: '0 auto'}}>
                    <Spinner />
                </div>
            )}
            {!initialLoad && <div className={style.notesContainer}>
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

                {state?.notes?.length > 0 ? (
                    <>
                        {state?.notes?.map((note) => {
                            return (
                                <Note
                                    key={note.id}
                                    note={note}
                                    onDelete={(id: number) => deleteNote(id)}
                                    onEdit={(id: number, content: string) => editNote(id, content)}
                                    isLoading={note.id === state.loadingId}
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
                    {state?.notes?.length > 0 && state.totalItems > state.perPage && (
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
            </div>}
        </>
    );
}

/**
 * @since 4.8.0 Improved accessibility with semantic buttons. Added per-note loading state handling.
 * @since 4.4.0
 */
const Note = ({note, onDelete, onEdit, isLoading}) => {
    const [showContextMenu, setShowContextMenu] = useState(false);
    const [currentlyEditing, setCurrentlyEditing] = useState();
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
                                        setCurrentlyEditing(null);
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
                            {isLoading ? (
                                    <div className={style.noteLoading}>
                                        <Spinner />
                                    </div>
                                ) : (
                                    <>
                                        <div className={style.note}>
                                            <div className={style.title}>
                                                {note.content}
                                            </div>

                                            <button
                                                className={style.dotsMenu}
                                                onClick={() => setShowContextMenu(true)}
                                                aria-haspopup="true"
                                                aria-expanded={showContextMenu}
                                                aria-controls="contextMenu"
                                            >
                                                <DotsMenuIcon />
                                                {showContextMenu && (
                                                    <div className={style.menu} role="menu"
                                                    id="contextMenu" >
                                                        <button
                                                            className={style.menuItem}
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                setShowContextMenu(false);
                                                                setCurrentlyEditing(note.id);
                                                            }}
                                                        >
                                                            <EditIcon /> {__('Edit', 'give')}
                                                        </button>
                                                        <button
                                                            className={cx(style.menuItem, style.delete)}
                                                            onClick={(e) => {
                                                                e.preventDefault();
                                                                setShowContextMenu(false);
                                                                setShowDeleteDialog(true);
                                                            }}
                                                        >
                                                            <DeleteIcon /> {__('Delete', 'give')}
                                                        </button>
                                                    </div>
                                                )}
                                            </button>
                                        </div>
                                        <div className={style.date}>
                                            {formatTimestamp(note.createdAt.date)}
                                        </div>
                                    </>
                                )}
                            </div>
                    </>
                )}
                <ConfirmationDialog
                    title={__('Delete Note', 'give')}
                    isOpen={showDeleteDialog}
                    handleClose={() => setShowDeleteDialog(false)}
                    handleConfirm={() => {
                        setShowDeleteDialog(false)
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

