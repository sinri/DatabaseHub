const PermissionsDetailReportPage = {
    template: `
        <layout-list>
            <div slot="search">
                <i-form action="javascript:;" inline>
                     <form-item>
                         <i-select placeholder="Apply User" style="width: 240px;" multiple clearable filterable
                                   v-model="query.user_list">
                            <i-option v-for="item in allUserList" 
                                :key="item.userId" 
                                :value="item">{{ item.realname }}({{ item.username }})</i-option>
                        </i-select>
                     </form-item>
                    
                     <form-item>
                         <i-button type="primary" html-type="submit"
                            :loading="userPermissionTable.isLoading"
                            @click="getUserPermission">Load</i-button>
                     </form-item>
                </i-form>
            </div>
            <template v-for="table in userPermissionTable.list">
                <i-table border style="margin-bottom: 20px;" 
                     :loading="userPermissionTable.isLoading"
                     :columns="table.columns" 
                     :data="table.data"></i-table>
            </template>
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
                list: []
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
        makeUserPermissionTableList () {
            const list = [];

            // 用户列
            const baseColumns = [{
                title: 'User',
                key: 'username',
                render: (h, {row}) => {
                    return h('div', `${row.user.realname}(${row.user.username})`);
                }
            }];

            Object.keys(this.userPermissionMap).forEach((userId) => {
                const userPermission = this.userPermissionMap[userId];

                // columns
                const columns = baseColumns.concat();

                Object.keys(userPermission).forEach((databaseId) => {
                    const database = userPermission[databaseId];

                    columns.push({
                        title: database.database_info.databaseName,
                        key: databaseId,
                        render: (h) => {
                            return h('div', {
                                style: {
                                    padding: '10px 0'
                                }
                            }, [
                                ...database.permissions.map((permission) => {
                                    return (h('div', {
                                        style: {
                                            padding: '5px 0'
                                        }
                                    }, [
                                        h('i-switch', {
                                            props: {
                                                disabled: true,
                                                value: true
                                            }
                                        }),
                                        h('span', {
                                            style: {
                                                marginLeft: '5px'
                                            }
                                        }, permission)
                                    ]));
                                })
                            ]);
                        }
                    });
                });

                // data
                const data =[{
                    user: this.allUserMap[userId],
                    ...Object.keys(userPermission).map((databaseId) => {
                        return {
                            [databaseId]: userPermission[databaseId].database_info
                        };
                    })
                }];

                list.push({
                    columns,
                    data
                })
            });

            return list;
        },
        setLoading (bool) {
            this.userPermissionTable.isLoading = bool;
        },
        formatQuery (query) {
            return {
                user_ids: query.user_list.map((item) => {
                    return item.userId
                }).join(','),
                database_id_list: this.permittedDatabases.map((item) => {
                    return item.databaseId
                })
            }
        },
        getUserPermission () {
            const query = {...this.query}

            this.setLoading(true);

            ajax('getUserPermission', this.formatQuery(query)).then(({dict}) => {
                this.userPermissionMap = dict;
                this.userPermissionTable.list = this.makeUserPermissionTableList()
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        getAllUserList () {
            this.allUserList = JSON.parse(localStorage.getItem('allUserList'));
        },
        getPermittedDatabases () {
            ajax('commonDatabaseList').then(({list}) => {
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
