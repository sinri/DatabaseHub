const API = {
    // CAS
    getLoginConfig: {
        url: 'CasController/getLoginConfig'
    },
    logout: {
        url: 'CasController/logout'
    },

    // Dingtalk
    getScanQRCodeLoginToken: {
        url: 'DingtalkLoginController/getScanQRCodeLoginToken'
    },
    checkScanQRCodeLoginStatus: {
        url: 'DingtalkLoginController/checkScanQRCodeLoginStatus',
        suffix: 'token'
    },

    // LoginController
    login: {
        url: 'LoginController/login'
    },
    getAllUser: {
        url: 'LoginController/getAllUser'
    },
    dashboardMeta: {
        url: 'LoginController/dashboardMeta'
    },

    // ApplicationController
    createApplication: {
        desc: '',
        url: 'ApplicationController/create'
    },
    editApplication: {
        desc: '',
        url: 'ApplicationController/update'
    },
    cancelApplication: {
        desc: '',
        url: 'ApplicationController/cancel'
    },
    denyApplication: {
        desc: '',
        url: 'ApplicationController/deny'
    },
    approveApplication: {
        desc: '',
        url: 'ApplicationController/approve'
    },
    searchApplication: {
        desc: '',
        url: 'ApplicationController/search'
    },
    getApplicationDetail: {
        desc: '',
        url: 'ApplicationController/detail'
    },
    myApplicationApprovals: {
        desc: '',
        url: 'ApplicationController/myApprovals'
    },
    downloadExportedContentAsCSV: {
        desc: '',
        url: 'ApplicationController/downloadExportedContentAsCSV'
    },

    // DatabaseManageController
    createDatabase: {
        desc: '',
        url: 'DatabaseManageController/add'
    },
    editDatabase: {
        desc: '',
        url: 'DatabaseManageController/edit'
    },
    getDatabaseDetail: {
        desc: '',
        url: 'DatabaseManageController/getDatabaseDetail'
    },
    removeDatabase: {
        desc: '',
        url: 'DatabaseManageController/remove'
    },
    commonDatabaseList: {
        desc: '',
        url: 'DatabaseManageController/commonList'
    },
    advanceDatabaseList: {
        desc: '',
        url: 'DatabaseManageController/advanceList'
    },
    createDatabaseAccount: {
        desc: '',
        url: 'DatabaseManageController/editAccount'
    },
    getDatabaseAccountDetail: {
        desc: '',
        url: 'DatabaseManageController/getDatabaseAccountDetail'
    },
    removeDatabaseAccount: {
        desc: '',
        url: 'DatabaseManageController/removeAccount'
    },
    setDatabaseDefaultAccount: {
        desc: 'Set one account as default of database.',
        url: 'DatabaseManageController/setDefaultAccount'
    },
    databaseAccountList: {
        desc: 'Fetch the accounts of database.',
        url: 'DatabaseManageController/databaseAccountList'
    },

    // PermissionManageController
    getUserPermission: {
        desc: 'Fetch the permissions of user with databases.',
        url: 'PermissionManageController/getUserPermission'
    },
    updateUserPermission: {
        desc: 'Update the permissions of user with databases.',
        url: 'PermissionManageController/updateUserPermission'
    },

    // QuickQueryController
    quickQueryPermittedDatabases: {
        desc: 'For quick query page, show the permitted databases.',
        url: 'QuickQueryController/permittedDatabases'
    },
    syncExecute: {
        desc: 'Run quick query sql as sync task.',
        url: 'QuickQueryController/syncExecute'
    },

    // KillerController
    killerPermittedDatabases: {
        desc: 'List the permitted databases.',
        url: 'KillerController/permittedDatabases'
    },
    showProcessList: {
        desc: 'Run `show full processlist` and fetch result. Kill would rely on the `ID` and `USER` (case might not determined).',
        url: 'KillerController/showProcessList'
    },
    killProcess: {
        desc: 'Kill one thread by tid, with certain account by username.',
        url: 'KillerController/kill'
    },

    // Workers
    checkWorkerStatus: {
        desc: 'Check worker status.',
        url: 'ApplicationController/checkWorkerStatus',
    }
};
