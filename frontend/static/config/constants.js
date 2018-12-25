const CONSTANTS = {
    APPLICATION_TYPES: [
        'READ',
        'MODIFY',
        'EXECUTE',
        'DDL',
    ],
    APPLICATION_STATUS: [
        'APPLIED',
        'DENIED',
        'CANCELLED',
        'APPROVED',
        'EXECUTING',
        'DONE',
        'ERROR'
    ],
    APPLICATION_STATUS_ICON_TYPE_MAP: {
        APPLIED: 'ios-help-buoy',
        DENIED: 'ios-hand',
        CANCELLED: 'ios-build',
        APPROVED: 'ios-color-wand',
        EXECUTING: 'ios-bug',
        DONE: 'ios-checkmark-circle-outline',
        ERROR: 'ios-close-circle-outline'
    },
    APPLICATION_STATUS_TAG_COLOR_MAP: {
        APPLIED: 'blue',
        DENIED: 'error',
        CANCELLED: 'default',
        APPROVED: 'success',
        EXECUTING: 'blue',
        DONE: 'default',
        ERROR: 'error'
    },
    APPLICATION_PARALLELABLE: [
        'YES',
        'NO'
    ],
    DATABASE_TYPE: [
        'MYSQL'
    ],
    DATABASE_STATUS: [
        'NORMAL',
        'DISABLED'
    ],
    DATABASE_USER_PERMISSION: [
        'READ',
        'MODIFY',
        'DDL',
        'EXECUTE',
        'QUICK_QUERY',
        'KILL'
    ],
    QUICK_QUERY_TYPE: [
        'SYNC',
        'ASYNC'
        ],
    USER_TYPE: [
        'ADMIN',
        'USER'
    ]
}
