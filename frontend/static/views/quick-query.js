const QuickQueryPage = {
    template: `
        <layout>
            <div>
                <i-form action="javascript:;" inline>
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" clearable filterable
                                   v-model.trim="executeParams.database_id">
                             <i-option v-for="item in permittedDatabases"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }} ({{ item.engine }})</i-option>
                         </i-select>
                     </form-item>
                     <form-item>
                         <i-button type="primary"
                            html-type="submit"
                            :loading="queryResult.isLoading"
                            @click="syncExecute">Sync Execute</i-button>
                     </form-item>
                     
                      <form-item style="display: block;">
                         <codemirror ref="codeMirror" style="font-size: 14px;"
                            :options="codeMirrorOptions"
                            v-model="executeParams.sql"></codemirror>
                     </form-item>
                </i-form>
            </div>
            
            <div class="query-result" v-if="!queryResult.isLoading && queryResult.data.data">
                <divider style="color: #999;">Query result</divider>
                <div style="margin-bottom: 20px;padding: 10px;background-color: #f7f7f9;text-transform: uppercase;">
                    <span>
                        <strong style="margin-right: 5px;">Done:</strong>
                        <span style="color: #e83e8c;">{{ queryResult.data.done }}</span>
                    </span>
                    <divider type="vertical" />
                    <span>
                        <strong style="margin-right: 5px;">Query time:</strong>
                        <span style="color: #e83e8c;">{{ queryResult.data.query_time }}</span>
                    </span>
                    <divider type="vertical" />
                    <span>
                        <strong style="margin-right: 5px;">Total time:</strong>
                        <span style="color: #e83e8c;">{{ queryResult.data.total_time }}</span>
                    </span>
                </div>
                <div class="error" v-if="!queryResult.data.done" style="margin-bottom: 20px;padding: 10px;color: #ed4014;background-color: #ffefe6;">
                    <pre>{{ JSON.stringify(queryResult.data.error, null, 4) }}</pre>
                </div>
                <native-table v-else
                    :loading="queryResult.isLoading"
                    :columns="resultTableColumns"
                    :data="this.queryResult.data.data"></native-table>
          
            </div>
        </layout>
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
            queryResult: {
                isLoading: false,
                data: {}
            },
            permittedDatabases: []
        }
    },
    computed: {
        resultTableColumns () {
            const columns = [];

            if (this.queryResult.data.data.length > 0) {
                Object.keys(this.queryResult.data.data[0]).forEach((key) => {
                    columns.push({
                        title: key,
                        key
                    })
                })
            }

            return columns;
        }
    },
    methods: {
        setLoading (bool) {
            this.queryResult.isLoading = bool;
        },
        syncExecute () {
            const data = JSON.parse(JSON.stringify(this.executeParams));
            const selection = this.$refs.codeMirror.codemirror.getSelection().trim();

            if (selection !== '') {
                data.sql = selection;
            }

            this.setLoading(true);

            ajax('syncExecute', data).then((res) => {
                this.queryResult.data = res;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        getPermittedDatabases () {
            ajax('quickQueryPermittedDatabases').then(({list}) => {
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
