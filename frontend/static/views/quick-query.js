const QuickQueryPage = {
    template: `
        <layout-list>
            <div slot="search">
                <i-form action="javascript:;" inline>
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" clearable
                                   v-model.trim="executeParams.database_id">
                             <i-option v-for="item in permittedDatabases"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                     <form-item>
                         <i-button type="primary"
                            html-type="submit"
                            :loading="queryResult.isLoading"
                            @click="syncExecute">Sync Execute</i-button>
                     </form-item>
                     
                      <form-item style="display: block;">
                         <codemirror style="font-size: 14px;"
                            :options="codeMirrorOptions"
                            v-model="executeParams.sql"></codemirror>
                     </form-item>
                </i-form>
            </div>
            
            <div class="query-result" v-if="!queryResult.isLoading && queryResult.data.data">
                <div>
                    <span>Done: {{ queryResult.data.done }}</span>
                    <span>QueryTime:    {{ queryResult.data.query_time }}</span>
                    <span>TotalTime:    {{ queryResult.data.total_time }}</span>
                </div>
                <codemirror class="auto-size-code-mirror" style="font-size: 14px;"
                            :options="codeMirrorOptions"
                            :value="JSON.stringify(queryResult.data.data, null, 4)"></codemirror>
            </div>
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
            queryResult: {
                isLoading: false,
                data: {}
            },
            permittedDatabases: []
        }
    },
    methods: {
        setLoading (bool) {
            this.queryResult.isLoading = bool;
        },
        syncExecute () {
            const data = JSON.parse(JSON.stringify(this.executeParams));

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
