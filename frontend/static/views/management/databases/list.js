const DatabaseListPage = {
    template: `
        <layout-list>
            <div slot="handle">
                <i-button type="primary"
                    @click="goCreateDatabase">Create Database</i-button>
            </div>
            
            <i-table border
                :loading="databaseTable.isLoading"
                :columns="databaseTable.columns" :data="databaseTable.data"></i-table>
            
            <!--<page slot="pagination" :total="100" />-->
        </layout-list>
    `,
    data () {
        return {
            query: {
                page: 1
            },
            databaseTable: {
                isLoading: false,
                columns: [
                    {
                        title: 'Database ID',
                        key: 'databaseId',
                        width: 120
                    },
                    {
                        title: 'Name',
                        key: 'databaseName'
                    },
                    {
                        title: 'Connection',
                        render: (h, {row}) => {
                            return h('span', `${row.host}:${row.port}`)
                        }
                    },
                    {
                        title: 'Engine',
                        key: 'engine',
                        width: 150
                    },
                    {
                        title: 'Status',
                        width: 150,
                        render: (h, {row}) => {
                            return h('span', [
                                h('status', {
                                    props: {
                                        status: row.status
                                    }
                                }),
                                row.status
                            ])
                        }
                    },
                    {
                        title: 'Action',
                        width: 150,
                        render: (h, {row}) => {
                            return h('div', {
                                class: 'btn-group'
                            }, [
                                h('i-button', {
                                    props: {
                                        size: 'small',
                                        type: 'primary'
                                    },
                                    on: {
                                        click: () => {
                                            this.goEditDatabase(row)
                                        }
                                    }
                                }, 'Edit'),
                                h('i-button', {
                                    props: {
                                        size: 'small',
                                        type: 'success'
                                    },
                                    on: {
                                        click: () => {
                                            this.goDatabaseAccountsPage(row)
                                        }
                                    }
                                }, 'Accounts')
                            ])
                        }
                    }
                ],
                data: []
            }
        };
    },
    methods: {
        setLoading (bool) {
            this.databaseTable.isLoading = bool;
        },
        search () {
            const query = JSON.parse(JSON.stringify(this.query));

            this.setLoading(true);
            ajax('advanceDatabaseList', query).then(({list}) => {
                this.databaseTable.data = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        goCreateDatabase () {
            this.$router.push({
                name: 'createDatabasePage'
            });
        },
        goEditDatabase (item) {
            const query = JSON.parse(JSON.stringify(item));

            this.$router.push({
                name: 'editDatabasePage',
                query
            });
        },
        goDatabaseAccountsPage (item) {
            const query = JSON.parse(JSON.stringify(item));

            this.$router.push({
                name: 'databaseAccountsPage',
                params: {
                    databaseId: query.databaseId
                },
                query
            });
        }
    },
    mounted () {
        this.search();
    }
};
