const PermissionAuditPage = {
    template: `
        <layout-list>
            <template v-for="table in userPermissionTable.list">
                <h3 style="padding: 5px 0;">{{ table.user.realname }}({{ table.user.username }})</h3>
                
                <i-table border style="margin-bottom: 30px;" 
                     :loading="userPermissionTable.isLoading"
                     :columns="table.columns" 
                     :data="table.data" v-if="table.hasData"></i-table>
                <p style="margin-bottom: 30px;" v-else>null</p>
            </template>
        </layout-list>
    `,
    data () {
        return {
            userPermissionTable: {
                isLoading: false,
                list: []
            },
            userPermissionMap: {},
            allUserList: [],
            permittedDatabases: [],
            permissionTagColorMap: {
                READ: 'success',
                MODIFY: 'primary',
                DDL: 'primary',
                EXECUTE: 'primary',
                QUICK_QUERY: 'success',
                KILL: 'warning'
            }
        }
    },
    computed: {
        allUserMap () {
            const map = {};

            this.allUserList.forEach((user) => {
                map[user.userId] = user;
            });

            return map;
        }
    },
    methods: {
        makeUserPermissionTableList () {
            const list = [];

            Object.keys(this.userPermissionMap).forEach((userId) => {
                const user =  this.allUserMap[userId];
                const userPermission = this.userPermissionMap[userId];

                // columns
                const columns = [{
                    title: 'Database',
                    key: 'databaseName'
                }, {
                    title: 'Permissions',
                    key: 'permissions',
                    render: (h, {row}) => {
                        return h('div', {
                            style: {
                                padding: '10px 0'
                            }
                        }, [
                            ...row.permissions.map((permission) => {
                                return h('tag', {
                                    props: {
                                        type: 'border',
                                        color: this.permissionTagColorMap[permission]
                                    }
                                }, permission);
                            })
                        ]);
                    }
                }];


                // data
                const data = Object.keys(userPermission).map((databaseId) => {
                    const row = userPermission[databaseId]

                    return {
                        databaseName: row.database_info.databaseName,
                        permissions: row.permissions
                    };
                });


                list.push({
                    columns,
                    user,
                    hasData: data.length > 0,
                    data
                })
            });

            return list;
        },
        setLoading (bool) {
            this.userPermissionTable.isLoading = bool;
        },
        getUserPermission () {
            const query = {
                user_ids: this.allUserList.map((item) => {
                    return item.userId
                }).join(','),
                database_id_list: this.permittedDatabases.map((item) => {
                    return item.databaseId
                })
            };

            this.setLoading(true);

            ajax('getUserPermission', query).then(({dict}) => {
                this.userPermissionMap = dict;
                this.userPermissionTable.list = this.makeUserPermissionTableList()
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        getAllUserList () {
            return new Promise((resolve) => {
                this.allUserList = JSON.parse(localStorage.getItem('allUserList'));
                resolve();
            });
        },
        getPermittedDatabases () {
            return ajax('commonDatabaseList').then(({list}) => {
                this.permittedDatabases = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        Promise.all([
            this.getAllUserList(),
            this.getPermittedDatabases()
        ]).then(() => {
            this.getUserPermission();
        })
    }
};
