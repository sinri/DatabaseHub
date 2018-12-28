const PermissionsPage = {
    template: `
        <layout-list>
            <div slot="search">
                <i-form action="javascript:;" inline>
                     <form-item>
                         <i-select placeholder="Apply User" style="width: 160px;" multiple clearable
                                   v-model="query.user_list">
                            <i-option v-for="item in allUserList" 
                                :key="item.userId" 
                                :value="item">{{ item.realname }}({{ item.username }})</i-option>
                        </i-select>
                     </form-item>
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" multiple clearable
                                   v-model="query.database_list">
                             <i-option v-for="item in permittedDatabases"
                                       :key="item.databaseId"
                                       :value="item">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                    
                     <form-item>
                         <i-button type="primary" html-type="submit"
                            :loading="userPermissionTable.isLoading"
                            @click="getUserPermission">Load</i-button>
                     </form-item>
                </i-form>
            </div>
            <i-table border 
                     :loading="userPermissionTable.isLoading"
                     :columns="userPermissionTable.columns" 
                     :data="userPermissionTable.data"></i-table>
        </layout-list>
    `,
    data () {
        return {
            query: {
                user_list: [],
                database_list: []
            },
            userPermissionTable: {
                isLoading: false,
                columns: [],
                data: []
            },
            userPermissionMap: {},
            allUserList: [],
            permittedDatabases: []
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
        makeUserPermissionTableColumns () {
            const columns = [{
                title: 'User',
                key: 'username',
                render: (h, {row}) => {
                    return h('div', `${row.user.realname}(${row.user.username})`);
                }
            }];

            this.query.database_list.forEach(({databaseId, databaseName}) => {
                columns.push({
                    title: databaseName,
                    key: databaseId,
                    render: (h, {row}) => {
                        const {userId} = row.user;

                        return h('div', [
                            ...CONSTANTS.DATABASE_USER_PERMISSIONS.map((permission) => {
                                const userPermission = this.userPermissionMap[userId]

                                return (h('div', [
                                    h('span', permission),
                                    h('i-switch', {
                                        on: {
                                            'on-change': (status) => {
                                                this.togglePermission({
                                                    userId: row.user.userId,
                                                    databaseId,
                                                    permission
                                                }, status);
                                            }
                                        },
                                        props: {
                                            value: typeof userPermission !== 'undefined' &&
                                                typeof userPermission[databaseId] !== 'undefined' &&
                                                userPermission[databaseId].permissions.includes(permission)
                                        }
                                    })
                                ]))
                            })
                        ]);
                    }
                });
            });

            return columns;
        },
        makeUserPermissionTableData () {
            const data = [];

            this.query.user_list.forEach((user) => {
                data.push({
                    user,
                    ...this.query.database_list.map((database) => {
                        return {
                            [database.databaseId]: database
                        }
                    })
                });
            });

            console.log(JSON.stringify(data, null, 4))

            return data;
        },
        togglePermission ({userId, databaseId, permission}, on) {
            const userPermission = this.userPermissionMap[userId]
            const permissions = typeof userPermission !== 'undefined' &&
                typeof userPermission[databaseId] !== 'undefined' &&
                userPermission[databaseId].permissions || [];

            if (on) {
                permissions.push(permission);
            } else {
                permissions.splice(permissions.indexOf(permission), 1);
            }

            ajax('updateUserPermission', {
                user_id: userId,
                database_id: databaseId,
                permissions
            }).then((res) => {
                console.log(res)
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        setLoading (bool) {
            this.userPermissionTable.isLoading = bool;
        },
        formatQuery (query) {
            return {
                user_ids: query.user_list.map((item) => {
                    return item.userId
                }).join(','),
                database_id_list: query.database_list.map((item) => {
                    return item.databaseId
                })
            }
        },
        getUserPermission () {
            const query = JSON.parse(JSON.stringify(this.query))

            this.setLoading(true);

            ajax('getUserPermission', this.formatQuery(query)).then(({dict}) => {
                this.userPermissionMap = dict;
                this.userPermissionTable.columns = this.makeUserPermissionTableColumns();
                this.userPermissionTable.data = this.makeUserPermissionTableData();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        getAllUserList () {
            ajax('getAllUser').then(({list}) => {
                this.allUserList = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        getPermittedDatabases () {
            ajax('permittedDatabases').then(({list}) => {
                this.permittedDatabases = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getAllUserList();
        this.getPermittedDatabases();
    }
};
