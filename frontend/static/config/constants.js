const CONSTANTS = {
    APPLICATION_TYPES: [
        'READ',
        'MODIFY',
        'EXECUTE',
        'DDL'
    ],
    APPLICATION_TYPES_ICON_TYPE_MAP: {
        READ: 'ios-search',
        MODIFY: 'ios-create',
        EXECUTE: 'md-arrow-dropright-circle',
        DDL: 'md-barcode'
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
        CANCELLED: 'md-arrow-round-back',
        DENIED: 'md-close',
        APPROVED: 'md-checkmark',
        EXECUTING: 'md-arrow-round-forward',
        DONE: 'ios-checkmark-circle',
        ERROR: 'ios-close-circle'
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
        'MYSQL',
        'ALIYUN_POLARDB',
        'ALIYUN_ADB'
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
