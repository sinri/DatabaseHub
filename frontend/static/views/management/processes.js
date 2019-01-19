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
                            @click="search">Show Full Process List</i-button>
                     </form-item>
                     <form-item>
                        <span>Processes in sleep status are hidden.</span>
                     </form-item>
                </i-form>
            </div>
            <native-table :loading="processTable.isLoading"
                     :columns="processTable.columns" 
                     :data="noSleepProcessTable"></native-table>
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
                        key: 'Info',
                        render: (h, {row}) => {
                            return h('div', {
                                class: ['pre-line'],
                                style: {
                                    maxWidth: '25vw',
                                    'max-height': '100px',
                                    overflow: 'auto',
                                }
                            }, row.Info)
                        }
                    },
                    {
                        title: 'Action',
                        width: 100,
                        render: (h, {row}) => {
                            return h('div', {
                                class: ['btn-group']
                            }, [
                                h('i-button', {
                                    on: {
                                        click: () => {
                                            this.previewProcess(row)
                                        }
                                    },
                                    props: {
                                        size: 'small',
                                        type: 'success'
                                    }
                                }, 'Detail'),
                                h('i-button', {
                                    on: {
                                        click: () => {
                                            this.killProcess(row)
                                        }
                                    },
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
        output (val, row, render) {
            if (typeof render !== 'undefined') {
                return render.call(this, val, row);
            }

            return val;
        },
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
        },
        killProcess (item) {
            ajax('killProcess', {
                database_id: this.query.database_id,
                username: item.User,
                tid: item.Id
            }).then(() => {
                this.search();
                SinriQF.iview.showSuccessMessage('Kill Process Success!', 2);
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        previewProcess (item) {
            this.$Modal.info({
                render: (h) => {
                    return h('pre', {
                        style: {
                            overflow: 'auto'
                        }
                    }, JSON.stringify(item, null, 4))
                }
            })
        }
    },
    mounted () {
        this.getPermittedDatabases();
    }
};
