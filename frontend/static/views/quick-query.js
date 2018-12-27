const QuickQueryPage = {
    template: `
        <layout-list>
            <div slot="search">
                <i-form action="javascript:;">
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" clearable
                                   v-model.trim="executeParams.database_id">
                             <i-option v-for="item in permittedDatabases"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                     
                      <form-item>
                         <codemirror style="font-size: 14px;"
                            :options="codeMirrorOptions"
                            v-model="executeParams.sql"></codemirror>
                     </form-item>
                    
                     <form-item style="text-align: right;">
                         <i-button type="primary" html-type="submit" @click="syncExecute">Sync Execute</i-button>
                     </form-item>
                </i-form>
            </div>
            <i-table border 
                     :loading="queryTable.isLoading"
                     :columns="queryTable.columns" 
                     :data="queryTable.data"></i-table>
        </layout-list>
    `,
    data () {
        return {
            executeParams: {
                database_id: '',
                sql: ''
            },
            codeMirrorOptions: {
                tabSize: 4,
                styleActiveLine: true,
                lineNumbers: true,
                line: true,
                mode: 'text/x-mysql',
                theme: 'panda-syntax'
            },
            queryTable: {
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
                        key: 'Id'
                    },
                    {
                        title: 'Account Username',
                        key: 'User'
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
    methods: {
        setLoading (bool) {
            this.queryTable.isLoading = bool;
        },
        syncExecute () {
            const data = JSON.parse(JSON.stringify(this.executeParams))

            this.setLoading(true);

            ajax('syncExecute', data).then(({list}) => {
                this.queryTable.data = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
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
        this.getPermittedDatabases();
    }
};
