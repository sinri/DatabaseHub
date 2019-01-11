const ApplicationListPage = {
    template: `
        <layout-list>
            <div slot="search">
                <div>
                    <i-button type="primary" @click="goCreateApplication">Create Application</i-button>
                    <divider dashed></divider>
                </div>
                <i-form action="javascript:;" inline style="float: right;">
                     <form-item>
                         <i-input type="text" placeholder="Title" v-model.trim="queryForm.title" />
                     </form-item>
                     <form-item>
                         <i-select placeholder="Database" style="width: 160px;" clearable
                                   v-model.trim="queryForm.database_id">
                             <i-option v-for="item in databaseList"
                                       :key="item.databaseId"
                                       :value="item.databaseId">{{ item.databaseName }}</i-option>
                         </i-select>
                     </form-item>
                     <form-item>
                         <i-select placeholder="Apply User" style="width: 160px;" clearable
                                   v-model.trim="queryForm.apply_user">
                            <i-option v-for="item in allUserList" 
                                :key="item.userId" 
                                :value="item.userId">{{ item.realname }}({{ item.username }})</i-option>
                        </i-select>
                     </form-item>
                     <form-item>
                         <i-button type="primary" html-type="submit" icon="ios-search"
                                   :loading="applicationTable.isLoading"
                                   @click="onSearch">Search</i-button>
                     </form-item>
                </i-form>
                <div style="display: flex;margin-bottom: -10px;">
                    <div class="filter-btn-group" style="margin-right: 40px;">
                        Type：
                        <tooltip v-for="item in CONSTANTS.APPLICATION_TYPES" placement="top"
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
                        <tooltip v-for="item in CONSTANTS.APPLICATION_STATUS" placement="top"
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
            <i-table border 
                     :loading="applicationTable.isLoading"
                     :columns="applicationTable.columns" 
                     :data="applicationTable.data"></i-table>
            
            <drawer width="700" :styles="{padding: 0}" :closable="false"
                v-model="previewer.drawerVisible">
                <application-preview ref="applicationDetail"
                    :application-id="previewer.applicationId"
                    @update="search"></application-preview>
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
                type: [],
                apply_user: '',
                status: []
            },
            query: {
                title: '',
                database_id: '',
                type: [],
                apply_user: '',
                status: [],
                page: 1,
                page_size: 10
            },
            queryCancelExecutor: null,
            applicationTable: {
                isLoading: false,
                columns: [
                    {
                        title: 'Application ID',
                        key: 'applicationId',
                        width: 120
                    },
                    {
                        title: 'Title',
                        key: 'title',
                        render: (h, {row}) => {
                            return h('a', {
                                class: ['text-ellipsis'],
                                attrs: {
                                    title: row.title
                                },
                                style: {
                                    display: 'block',
                                    lineHeight: '45px'
                                },
                                on: {
                                    click: () => {
                                        this.goDetailApplication(row)
                                    }
                                }
                            }, row.title)
                        }
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
                            return h('div', `${row.applyUser.realname}(${row.applyUser.username})`)
                        }
                    },
                    {
                        title: 'Applied Time',
                        width: 180,
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
                                    on: {
                                        click: () => {
                                            this.goDetailApplication(row)
                                        }
                                    },
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
        setLoading (bool) {
            this.applicationTable.isLoading = bool;
        },
        search (params = {}) {
            // 取消前面的查询
            if (this.queryCancelExecutor) {
                this.queryCancelExecutor();
            }

            Object.assign(this.query, params);

            const query = JSON.parse(JSON.stringify(this.query));

            this.setLoading(true);
            ajax('searchApplication', query, (c) => {
                this.queryCancelExecutor = c;
            }).then(({list, total}) => {
                this.applicationTable.data = list;
                this.applicationTable.total = total;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        },
        goCreateApplication () {
            this.$router.push({
                name: 'createApplicationPage'
            });
        },
        goEditApplication ({applicationId}) {
            this.$router.push({
                name: 'editApplicationPage',
                query: {
                    applicationId
                }
            });
        },
        goDetailApplication ({applicationId}) {
            const {href} = router.resolve({
                name: 'detailApplicationPage',
                query: {
                    applicationId
                }
            });

            window.open(href, '_blank');
        },
        previewApplication (item) {
            this.previewer.applicationId = item.applicationId;
            this.previewer.drawerVisible = true;

            this.$nextTick(() => {
                this.$refs.applicationDetail.init()
            })
        },
        getAllUserList () {
            this.allUserList = JSON.parse(localStorage.getItem('allUserList'));
        },
        getDatabaseList () {
            ajax('commonDatabaseList').then(({list}) => {
                this.databaseList = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    created () {
        const isAdmin = JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).userType === 'ADMIN';

        if (!isAdmin) {
            this.queryForm.apply_user = JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).userId;
        }
    },
    mounted () {
        this.onSearch();
        this.getAllUserList();
        this.getDatabaseList();
    }
};
