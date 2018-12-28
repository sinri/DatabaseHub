const CONSTANTS = {
    APPLICATION_TYPES: [
        'READ',
        'MODIFY',
        'EXECUTE',
        'DDL'
    ],
    APPLICATION_TYPES_ICON_TYPE_MAP: {
        READ: 'md-color-filter',
        MODIFY: 'md-code-working',
        EXECUTE: 'ios-build',
        DDL: 'md-filing'
    },
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
        APPLIED: 'ios-send',
        DENIED: 'ios-thumbs-down',
        CANCELLED: 'ios-pause',
        APPROVED: 'ios-thumbs-up',
        EXECUTING: 'ios-flash',
        DONE: 'md-checkmark',
        ERROR: 'md-close'
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
    DATABASE_USER_PERMISSIONS: [
        'READ',
        'MODIFY',
        'DDL',
        'EXECUTE',
        'QUICK_QUERY',
        'KILL'
    ],
    QUICK_QUERY_TYPES: [
        'SYNC',
        'ASYNC'
        ],
    USER_TYPES: [
        'ADMIN',
        'USER'
    ]
}
