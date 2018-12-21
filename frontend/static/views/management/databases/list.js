const DatabaseListPage = {
    template: `
        <layout-list>
            <i-form slot="search" inline>
                <form-item>
                    <i-input type="text" placeholder="请输入数据库名称" />
                </form-item>
            </i-form>
            
            <div slot="handle">
                <i-button type="primary" @click="goCreateDatabase">创建数据库</i-button>
            </div>
            
            <i-table border :columns="databaseTable.columns" :data="databaseTable.data"></i-table>
            
            <page slot="pagination" :total="100" />
        </layout-list>
    `,
    data () {
        return {
            query: {},
            databaseTable: {
                columns: [
                    {
                        title: 'Database ID',
                        key: 'database_id'
                    },
                    {
                        title: 'Name',
                        key: 'database_name'
                    },
                    {
                        title: 'Connection',
                        render (h, {row}) {
                            return h('span', `${row.host}:${row.port}`)
                        }
                    },
                    {
                        title: 'Engine',
                        key: 'engine'
                    },
                    {
                        title: 'Status',
                        key: 'status'
                    },
                    {
                        title: 'Action',
                        render (h, {row}) {
                            return h('button', `${row.host}:${row.port}`)
                        }
                    }
                ],
                data: []
            }
        }
    },
    methods: {
        search () {
            ajax('commonDatabaseList').then(({list}) => {
                this.databaseTable.data = list
            }).catch((error, status) => {
                SinriQF.iview.showErrorMessage('Get Database List Error. Feedback: ' + error + ' Status: ' + status, 5);
            })
        },
        goCreateDatabase () {
            this.$router.push({
                name: 'createDatabasePage'
            })
        }
    },
    mounted () {
        this.search()
    }
};
