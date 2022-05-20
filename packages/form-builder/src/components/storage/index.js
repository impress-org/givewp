import LocalStorage from './local'

const fallbackStorage = LocalStorage

const Storage = window.storage || fallbackStorage

export default Storage
