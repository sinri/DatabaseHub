const API = {
    // LoginController
    login: {
        url: 'LoginController/login'
    },

    // ApplicationController
    myApprovals: {
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
    }
}
