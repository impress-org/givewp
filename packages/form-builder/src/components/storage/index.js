import LocalStorage from './local'
import NullStorage from './null'
import AbstractStorage from './abstract'
import debug from '../debug'

const fallbackStorage = {
    'testing': NullStorage,
    'development': LocalStorage,
    'production': AbstractStorage,
}[process.env.NODE_ENV]

debug( process.env.NODE_ENV )
debug( fallbackStorage )

const Storage = window.storage || fallbackStorage

export default Storage
