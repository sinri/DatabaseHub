const ApplicationListPage = {
    // language=HTML
    template: `
        <layout-list>
            <div slot="search">
                <i-form inline>
                     <form-item>
                         <i-input type="text" placeholder="Title" v-model.trim="queryForm.title" />
                     </form-item>
                     <form-item>
                         <i-select v-model.trim="queryForm.database_id">
                             <i-option v-for="item in databaseList"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                     <form-item>
                         <i-select v-model.trim="queryForm.apply_user">
                            <i-option v-for="item in allUserList" 
                                :key="item" 
                                :value="item">{{ item }}</i-option>
                        </i-select>
                     </form-item>
                     <form-item>
                         <i-button type="primary" icon="ios-search" @click="onSearch">Search</i-button>
                         <i-button @click="goCreateApplication">Create Application</i-button>
                     </form-item>
                </i-form>
            </div>
            <div slot="handle">
                <div style="display: flex;margin-bottom: -10px;">
                    <div class="filter-btn-group" style="margin-right: 40px;">
                        Type：
                        <tooltip v-for="item in CONSTANTS.APPLICATION_TYPES"
                                 :key="item"
                                 :content="item">
                            <i-button shape="circle"
                                      :icon="CONSTANTS.APPLICATION_TYPES_ICON_TYPE_MAP[item]"
                                      :type="queryForm.type.indexOf(item) !== -1 ? 'primary' : 'default'"
                                      @click="toggleQueryForm('type', item)"></i-button>
                        </tooltip>
                    </div>
                    <div class="filter-btn-group">
                        Status：
                        <tooltip v-for="item in CONSTANTS.APPLICATION_STATUS"
                                 :key="item"
                                 :content="item">
                            <i-button shape="circle"
                                      :icon="CONSTANTS.APPLICATION_STATUS_ICON_TYPE_MAP[item]"
                                      :type="queryForm.status.indexOf(item) !== -1 ? 'primary' : 'default'"
                                      @click="toggleQueryForm('status', item)"></i-button>
                        </tooltip>
                    </div>
                </div>
            </div>
            
            <i-table border :columns="applicationTable.columns" :data="applicationTable.data"></i-table>
            
            <drawer width="700" :styles="{padding: 0}"
                :mask="false"
                v-model="previewer.drawerVisible">
                <application-detail ref="applicationDetail" :application-id="previewer.applicationId"></application-detail>
            </drawer>
            
            <page slot="pagination" show-total show-elevator
                :total="applicationTable.total"
                @on-change="changePage"
                v-show="applicationTable.total > 0" />
        </layout-list>
    `,
    data () {
        return {
            queryForm: {
                title: '',
                database_id: '',
                type: [
                    'READ',
                    'MODIFY',
                    'EXECUTE',
                    'DDL'
                ],
                apply_user: '',
                status: [
                    'APPLIED',
                    'DENIED',
                    'CANCELLED',
                    'APPROVED',
                    'EXECUTING',
                    'DONE',
                    'ERROR'
                ]
            },
            query: {
                title: '',
                database_id: '',
                type: [
                    'READ',
                    'MODIFY',
                    'EXECUTE',
                    'DDL'
                ],
                apply_user: '',
                status: [
                    'APPLIED',
                    'DENIED',
                    'CANCELLED',
                    'APPROVED',
                    'EXECUTING',
                    'DONE',
                    'ERROR'
                ],
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
                        render: (h, {row}) => {
                            return h('tag', {
                                props: {
                                    color: this.$options.filters.getApplicationStatusTagColor(row.status)
                                }
                            }, row.status)
                        }
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
            allUserList: [],
            databaseList: [],
            previewer: {
                applicationId: 0,
                drawerVisible: false
            }
        };
    },
    methods: {
        toggleQueryForm (key, value) {
            const index = this.queryForm[key].indexOf(value)

            if (index === -1) {
                this.queryForm[key].push(value)
            } else {
                this.queryForm[key].splice(index, 1)
            }

            this.onSearch();
        },
        onSearch () {
            this.search({
                ...JSON.parse(JSON.stringify(this.queryForm)),
                page: 1
            });
        },
        changePage (page) {
            this.search({page});
        },
        search (params = {}) {
            Object.assign(this.query, params);

            const query = JSON.parse(JSON.stringify(this.query));

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
            this.previewer.applicationId = item.applicationId;
            this.previewer.drawerVisible = true;

            this.$nextTick(() => {
                this.$refs.applicationDetail.init()
            })
        },
        getAllUserList () {
            ajax('getAllUser').then(({list}) => {
                this.allUserList = list;
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
        this.getAllUserList();
        this.getDatabaseList();
    }
};
