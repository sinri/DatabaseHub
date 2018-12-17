const ConfigPage = {
    template: `
    <Row>
        <i-col span="24" style="margin: 10px">
            <Tabs>
                <Tab-Pane label="Users">
                    <p>
                        According to the policy of user, the user management varies.
                        Please contact the administrator for details.
                    </p>
                </Tab-Pane>
                <Tab-Pane label="Privileges">
                    <p>
                        Here you can decide what jobs one can do.
                    </p>
                </Tab-Pane>
                <Tab-Pane label="Databases">
                    <p>
                        Here register your databases and accounts.
                        <!--<i-button type="text" @click="submitDatabaseEditModal"></i-button>-->
                    </p>
                    <Modal v-model="openDatabaseEditModal" :title="databaseEditModalTitle"
                           :loading="databaseEditModalLoading" @on-ok="submitDatabaseEditModal">
                        <p>After you click ok, the dialog box will close in 2 seconds.</p>
                    </Modal>
                </Tab-Pane>
            </Tabs>
        </i-col>
    </Row>
    `,
    data () {
        return {
            databaseEditData: null,
            openDatabaseEditModal: false,
            databaseEditModalTitle: '',
            databaseEditModalLoading: true
        };
    },
    methods: {
        openNewDatabaseEditModal () {
            this.databaseEditData = {
                database_id: null,
                database_name: '',
                host: '',
                port: '3306',
                status: 'NORMAL',
                engine: 'MYSQL',
            };
            this.openDatabaseEditModal = true;
        },
        submitDatabaseEditModal () {
            SinriQF.api.call(
                'DatabaseManageController/add'
            );
        }
    },
    mounted () {
        SinriQF.config.TokenName = 'database_hub_token';
        SinriQF.config.vueInstance = this;
    }
};
