const ApprovalListPage = {
    template: `
        <layout-list>
            <i-table border
                :loading="approvalTable.isLoading"
                :columns="approvalTable.columns"
                :data="approvalTable.data"></i-table>
            
            <page slot="pagination" show-total show-elevator
                :total="approvalTable.total"
                @on-change="changePage"
                v-show="approvalTable.total > 0" />
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
            approvalTable: {
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
        setLoading (bool) {
            this.approvalTable.isLoading = bool;
        },
        search (params = {}) {
            Object.assign(this.query, params);

            const query = JSON.parse(JSON.stringify(this.query));

            this.setLoading(true);

            ajax('myApplicationApprovals', query).then(({list, total}) => {
                this.approvalTable.data = list;
                this.approvalTable.total = total;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.setLoading(false);
            });
        }
    },
    mounted () {
        this.search();
    }
};
