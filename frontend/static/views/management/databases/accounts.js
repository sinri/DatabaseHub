const DatabaseAccountsPage = {
    template: `
        <layout-list>
            <div slot="header">
                <h2 class="title"><i-button @click="back">返回</i-button>  Manage accounts for database #{{ $route.params.databaseId }} - {{ $route.query.databaseName }}</h2>
                <divider></divider>
            </div>
            
            <div slot="handle">
                <i-form ref="databaseAccountForm" inline
                    :model="databaseAccountForm.model" 
                    :rules="databaseAccountForm.rules"
                    >
                    <form-item prop="username">
                        <i-input clearable placeholder="username" v-model.trim="databaseAccountForm.model.username" />
                    </form-item>
                    
                    <form-item prop="password">
                        <i-input autocomplete="new-password" clearable type="password" placeholder="Password" v-model.trim="databaseAccountForm.model.password" />
                    </form-item>
                    
                    <form-item>
                        <i-button type="primary" @click="onDatabaseAccountFormSubmit">Register new account</i-button>
                    </form-item>
                </i-form>
            </div>
            
            <i-table border :columns="databaseAccountTable.columns" :data="databaseAccountTable.data"></i-table>
            
            <!--<page slot="pagination" :total="100" />-->
        </layout-list>
    `,
    data () {
        return {
            query: {
                database_id: this.$route.params.databaseId,
            },
            databaseAccountForm: {
                model: {
                    database_id: this.$route.params.databaseId,
                    username: '',
                    password: ''
                },
                rules: {
                    username: [
                        {required: true, message: '不能为空'}
                    ],
                    password: [
                        {required: true, message: '不能为空'}
                    ]
                }
            },
            databaseAccountTable: {
                columns: [
                    {
                        title: 'Account ID',
                        key: 'accountId'
                    },
                    {
                        title: 'Username',
                        key: 'username'
                    },
                    {
                        title: 'IS DEFAULT',
                        render: (h, {row}) => {
                            if (row.accountId === this.databaseAccountTable.defaultAccount.accountId)
                                return h('tag', {
                                    props: {
                                        color: 'success'
                                    }
                                }, 'Yes')
                            else
                                return h('tag', {
                                    props: {
                                        color: 'default'
                                    }
                                }, 'No')
                        }
                    },
                    {
                        title: 'Action',
                        width: 200,
                        render: (h, {row}) => {
                            return h('div', {
                                class: 'btn-group'
                            }, [
                                h('i-button', {
                                    props: {
                                        size: 'small',
                                        type: 'error'
                                    },
                                    on: {
                                        click: () => {
                                            this.removeDatabaseAccount(row)
                                        }
                                    }
                                }, 'Remove'),
                                h('i-button', {
                                    props: {
                                        size: 'small',
                                        type: 'success'
                                    },
                                    on: {
                                        click: () => {
                                            this.setDatabaseDefaultAccount(row)
                                        }
                                    }
                                }, 'Set As Default')
                            ])
                        }
                    }
                ],
                data: [],
                defaultAccount: {}
            }
        };
    },
    methods: {
        back () {
            this.$router.replace({
                name: 'databaseListPage'
            });
        },
        search () {
            const query = JSON.parse(JSON.stringify(this.query));

            ajax('databaseAccountList', query).then((res) => {
                this.databaseAccountTable.data = res.accounts;
                this.databaseAccountTable.defaultAccount = res.default || {};
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        onDatabaseAccountFormSubmit () {
            this.$refs.databaseAccountForm.validate((valid) => {
                if (valid) {
                    this.createDatabaseAccount();
                }
            });
        },
        resetDatabaseAccountForm () {
            this.$refs.databaseAccountForm.resetFields()
        },
        createDatabaseAccount () {
            const data = JSON.parse(JSON.stringify(this.databaseAccountForm.model))

            ajax('createDatabaseAccount', data).then(() => {
                SinriQF.iview.showSuccessMessage('Register New Account Success!', 3);
                this.resetDatabaseAccountForm();
                this.search();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        removeDatabaseAccount (item) {
            ajax('removeDatabaseAccount', {
                database_id: this.$route.params.databaseId,
                account_id: item.accountId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Remove Account Success!', 3);
                this.search();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        setDatabaseDefaultAccount (item) {
            ajax('setDatabaseDefaultAccount', {
                database_id: this.$route.params.databaseId,
                account_id: item.accountId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Set Default Account Success!', 3);
                this.search();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.search();
    }
};
