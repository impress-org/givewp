import {__} from '@wordpress/i18n';
import useSWR from 'swr';
import {Dispatch, SetStateAction, useState} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {DotsMenuIcon, NotesIcon, EditIcon, DeleteIcon} from './Icons';
import Spinner from '../Spinner';
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
        currentlyEditing: null,
        note: '',
    });

    const [isAddingNote, setIsAddingNote] = context;

    const {
        data,
        isLoading,
        mutate,
    } = useSWR<[]>(endpoint, (url) => apiFetch({path: url}), {revalidateOnFocus: false});

    const saveNote = () => {
        setState({isSavingNote: true});
        apiFetch({path: endpoint, method: 'POST', data: {content: state.note}})
            .then((response) => {
                mutate(response).then(() => {
                    setIsAddingNote(false);
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

    if (isLoading) {
        return (
            <div style={{margin: '0 auto'}}>
                <Spinner />
            </div>
        );
    }

    if (!data?.length) {
        return (
            <div style={{margin: '0 auto'}}>
                <NotesIcon />
                <p>{__('No notes yet', 'give')}</p>
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
                            onClick={() => saveNote()}
                        >
                            {__('Save', 'give')}
                        </button>
                    </div>
                </div>
            )}
            {data.map((note) => {
                return (
                    <Note
                        note={note}
                    />
                )
            })}
        </div>
    );
}


/**
 * @unreleased
 */
const Note = ({note}) => {

    const [showMenuIcon, setShowMenuIcon] = useState(false);
    const [showContextMenu, setShowContextMenu] = useState(false);

    return (
        <div
            className={style.noteContainer}
            onMouseEnter={() => setShowMenuIcon(true)}
            onMouseLeave={() => {
                setShowMenuIcon(false);
                setShowContextMenu(false);
            }}
        >
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
                                    onClick={() => {
                                        // updateStatus('active');
                                        // dispatch.dismissNotification('update-archive-notice');
                                    }}
                                >
                                    <EditIcon /> {__('Edit', 'give')}
                                </a>
                                <a
                                    href="#"
                                    className={cx(style.menuItem, style.delete)}
                                    onClick={() => {}}
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
        </div>
    );
};
