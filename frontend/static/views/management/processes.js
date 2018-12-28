const ProcessesPage = {
    template: `
        <layout-list>
            <div slot="search">
                <i-form action="javascript:;" inline>
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" clearable
                                   v-model.trim="query.database_id">
                             <i-option v-for="item in permittedDatabases"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                    
                     <form-item>
                         <i-button type="primary" html-type="submit" icon="ios-search"
                            :loading="processTable.isLoading"
                            @click="search">Search</i-button>
                     </form-item>
                </i-form>
            </div>
            <i-table border 
                     :loading="processTable.isLoading"
                     :columns="processTable.columns" 
                     :data="noSleepProcessTable"></i-table>
        </layout-list>
    `,
    data () {
        return {
            query: {
                database_id: '',
                page: 1,
                pageSize: 10,
            },
            processTable: {
                isLoading: false,
                columns: [
                    {
                        type: 'expand',
                        width: 50,
                        render: (h, params) => {
                            return h('pre', JSON.stringify(params.row, null, 4))
                        }
                    },
                    {
                        title: 'Process ID',
                        key: 'Id',
                        width: 100
                    },
                    {
                        title: 'User',
                        key: 'User'
                    },
                    {
                        title: 'Host',
                        key: 'Host',
                        width: 160
                    },
                    {
                        title: 'DB',
                        key: 'db'
                    },
                    {
                        title: 'Command',
                        key: 'Command'
                    },
                    {
                        title: 'Time',
                        key: 'Time'
                    },
                    {
                        title: 'State',
                        key: 'State'
                    },
                    {
                        title: 'Info',
                        key: 'Info'
                    },
                    {
                        title: 'Action',
                        width: 100,
                        render: (h, {row}) => {
                            return h('div', [
                                h('i-button', {
                                    props: {
                                        size: 'small',
                                        type: 'error'
                                    }
                                }, 'KILL')
                            ])
                        }
                    }
                ],
                data: []
            },
            permittedDatabases: []
        }
    },
    computed: {
        noSleepProcessTable () {
            return this.processTable.data.filter((item) => {
                return !item.Command || item.Command.toLowerCase() !== 'sleep'
            })
        }
    },
    methods: {
        setLoading (bool) {
            this.processTable.isLoading = bool;
        },
        search () {
            const query = JSON.parse(JSON.stringify(this.query))

            this.setLoading(true);

            ajax('showProcessList', query).then(({list}) => {
                this.processTable.data = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        getPermittedDatabases () {
            ajax('killerPermittedDatabases').then(({list}) => {
                this.permittedDatabases = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getPermittedDatabases();
    }
};
