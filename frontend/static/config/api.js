const API = {
    // LoginController
    login: {
        url: 'LoginController/login'
    },
    getAllUser: {
        url: 'LoginController/getAllUser'
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
    detailApplication: {
        desc: '',
        url: 'ApplicationController/detail'
    },
    myApplicationApprovals: {
        desc: '',
        url: 'ApplicationController/myApprovals'
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
    permittedDatabases: {
        desc: 'For quick query page, show the permitted databases.',
        url: 'QuickQueryController/permittedDatabases'
    },
    syncExecute: {
        desc: 'Run quick query sql as sync task.',
        url: 'QuickQueryController/syncExecute'
    },

    // KillerController
    // permittedDatabases: {
    //     desc: 'List the permitted databases.',
    //     url: 'KillerController/permittedDatabases'
    // },
    showProcessList: {
        desc: 'Run `show full processlist` and fetch result. Kill would rely on the `ID` and `USER` (case might not determined).',
        url: 'KillerController/showProcessList'
    },
    kill: {
        desc: 'Kill one thread by tid, with certain account by username.',
        url: 'KillerController/kill'
    }
}
