const ApplicationListPage = {
    template: `
        <layout-list>
            <i-form slot="search" inline>
                 <form-item>
                     <i-input type="text" placeholder="Title" />
                 </form-item>
                 <form-item>
                     <i-input type="text" placeholder="Database ID" />
                 </form-item>
                 <form-item>
                     <i-select>
                        <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                 </form-item>
                 <form-item>
                     <i-input type="text" placeholder="Apply User" />
                 </form-item>
                 <form-item>
                    <i-select>
                        <i-option v-for="item in CONSTANTS.APPLICATION_STATUS" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                 </form-item>
                 <form-item>
                    <i-button type="primary" icon="ios-search" @click="search">Search</i-button>
                 </form-item>
            </i-form>
            </div>
            <div slot="handle">
                <i-button type="primary" @click="goCreateApplication">Create Application</i-button>
            </div>
            
            <i-table border :columns="applicationTable.columns" :data="applicationTable.data"></i-table>
            
            <drawer width="700"
                :mask="false"
                v-model="previewer.drawerVisible">
                <application-detail :data="previewer.application"></application-detail>
            </drawer>
            
            <page slot="pagination" show-total show-elevator
                :total="applicationTable.total"
                @on-change="changePage"
                v-show="applicationTable.total > 0" />
        </layout-list>
    `,
    data () {
        return {
            query: {
                title: '',
                database_id: '',
                type: [],
                apply_user: '',
                status: [],
                page: 1,
                page_size: 10
            },
            applicationTable: {
                columns: [
                    {
                        title: 'Application ID',
                        key: 'applicationId',
                        width: 120
                    },
                    {
                        title: 'Title',
                        key: 'title'
                    },
                    {
                        title: 'Database',
                        render: (h, {row}) => {
                            return h('div', row.database.databaseName)
                        }
                    },
                    {
                        title: 'Type',
                        key: 'type'
                    },
                    {
                        title: 'Status',
                        key: 'status'
                    },
                    {
                        title: 'Applicant',
                        render: (h, {row}) => {
                            return h('div', row.applyUser.realname)
                        }
                    },
                    {
                        title: 'Applied Time',
                        width: 150,
                        key: 'createTime'
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
                                    }
                                }, 'Open'),
                                h('i-button', {
                                    on: {
                                        click: () => {
                                            this.previewApplication(row)
                                        }
                                    },
                                    props: {
                                        size: 'small',
                                        type: 'success'
                                    }
                                }, 'Preview')
                            ])
                        }
                    }
                ],
                data: [],
                total: 0
            },
            databaseList: [],
            previewer: {
                drawerVisible: false,
                application: {}
            }
        };
    },
    methods: {
        changePage (page) {
            this.search({page});
        },
        search (params = {}) {
            const query = JSON.parse(JSON.stringify(this.query));

            Object.assign(query, params);

            ajax('searchApplication', query).then(({list, total}) => {
                this.applicationTable.data = list;
                this.applicationTable.total = total;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        goCreateApplication () {
            this.$router.push({
                name: 'createApplicationPage'
            });
        },
        goEditApplication (item) {
            const query = JSON.parse(JSON.stringify(item));

            this.$router.push({
                name: 'editApplicationPage',
                query
            });
        },
        previewApplication (item) {
            this.previewer.drawerVisible = true;

            ajax('detailApplication', {
                application_id: item.applicationId
            }).then(({application}) => {
                this.previewer.application = application
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        getDatabaseList () {
            ajax('commonDatabaseList').then(({list}) => {
                this.databaseList = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.search();
    }
};
