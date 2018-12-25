const ApprovalListPage = {
    template: `
        <layout-list>
            <i-table border :columns="applicationTable.columns" :data="applicationTable.data"></i-table>
            
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

            ajax('myApplicationApprovals', query).then(({list, total}) => {
                this.applicationTable.data = list;
                this.applicationTable.total = total;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.search();
    }
};
