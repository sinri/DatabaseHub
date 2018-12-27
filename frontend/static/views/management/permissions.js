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
                         <i-button type="primary" html-type="submit" icon="ios-search" @click="search">Search</i-button>
                     </form-item>
                </i-form>
            </div>
            <i-table border 
                     :loading="userPermissionTable.isLoading"
                     :columns="userPermissionTableColumns" 
                     :data="userPermissionTableData"></i-table>
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
        },
        userPermissionTableColumns () {
            const columns = [{
                title: 'User',
                key: 'username'
            }];

            this.query.database_list.forEach((database) => {
                columns.push({
                    title: database.databaseName
                });
            });

            return columns;
        },
        userPermissionTableData () {
            const data = [];

            this.userPermissionTable.data.forEach((user) => {
                data.push({
                    user,
                    username: user.username
                });
            });

            return data;
        }
    },
    methods: {
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
        search () {
            const query = JSON.parse(JSON.stringify(this.query))

            this.setLoading(true);

            ajax('getUserPermission', this.formatQuery(query)).then(({list}) => {
                this.userPermissionTable.data = list;
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
