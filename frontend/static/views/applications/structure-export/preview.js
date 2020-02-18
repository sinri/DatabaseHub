Vue.component('structure-export-application-preview', {
    template: `
        <spin fix v-if="isLoading"></spin>
        <layout-drawer v-else>
            <div slot="header" style="display: flex;align-items: center;">
                <div style="flex: 1;overflow: hidden;margin-left: 10px;">
                    <h2 class="title">
                        Structure Export Application #{{ applicationId }}
                        <tag :color="detail.application.status | getApplicationStatusTagColor">{{ detail.application.status }}</tag>
                    </h2>
                    <h3 class="sub-title text-ellipsis" :title="detail.application.title">{{ detail.application.title }}</h3>
                </div>
                <div>Applied by {{detail.application.applyUser.realname}}({{detail.application.applyUser.username}})</div>
            </div>
            <div style="display: flex;padding: 10px;background-color: rgb(247, 247, 249);text-transform: uppercase;">
                <div style="flex: auto;"><strong style="margin-right: 5px;">Type:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.type }}</span></div>
                <div style="flex: auto;"><strong style="margin-right: 5px;">Database:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.database.databaseName }} ({{ detail.application.database.engine }})</span></div>
                <div style="flex: auto;"><strong style="margin-right: 5px;">Create time:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.createTime }}</span></div>
            </div>
            <p style="margin: 10px 0;padding: 5px;">{{ detail.application.description }}</p>
            <i-form ref="form"
                style="padding: 20px 10px 10px;background-color: rgb(247, 247, 249);"
                :label-width="160"
                :model="detail.application.sql" 
            >
                <form-item label="Schema">{{ detail.application.sql.schema }}</form-item>

                <form-item label="Show Create Database">
                    <i-switch disabled v-model="detail.application.sql.show_create_database" />
                </form-item>

                <form-item label="Drop If Exist">
                    <i-switch disabled v-model="detail.application.sql.drop_if_exist" />
                </form-item>

                <form-item label="Reset Auto Increment">
                    <i-switch disabled v-model="detail.application.sql.reset_auto_increment" />
                </form-item>

                <form-item label="TABLES" v-if="detail.application.sql.show_create_table.length > 0">
                    <template v-if="detail.application.sql.show_create_table === 'ALL'">ALL</template>
                    <template v-else>
                        <Tag v-for="item in detail.application.sql.show_create_table.slice(0, 20)" :key="item">{{ item }}</Tag>
                        <span v-if="detail.application.sql.show_create_table.length > 20">...</span>
                    </template>
                </form-item>

                <form-item label="FUNCTIONS" v-if="detail.application.sql.show_create_function.length > 0">
                    <template v-if="detail.application.sql.show_create_function === 'ALL'">ALL</template>
                    <template v-else>
                        <Tag v-for="item in detail.application.sql.show_create_function.slice(0, 20)" :key="item">{{ item }}</Tag>
                        <span v-if="detail.application.sql.show_create_function.length > 20">...</span>
                    </template>
                </form-item>

                <form-item label="PROCEDURES" v-if="detail.application.sql.show_create_procedure.length > 0">
                    <template v-if="detail.application.sql.show_create_procedure === 'ALL'">ALL</template>
                    <template v-else>    
                        <Tag v-for="item in detail.application.sql.show_create_procedure.slice(0, 20)" :key="item">{{ item }}</Tag>
                        <span v-if="detail.application.sql.show_create_procedure.length > 20">...</span>
                    </template>
                </form-item>

                <form-item label="TRIGGERS" v-if="detail.application.sql.show_create_trigger.length > 0">
                    <template v-if="detail.application.sql.show_create_trigger === 'ALL'">ALL</template>
                    <template v-else>
                        <Tag v-for="item in detail.application.sql.show_create_trigger.slice(0, 20)" :key="item">{{ item }}</Tag>
                        <span v-if="detail.application.sql.show_create_trigger.length > 20">...</span>
                    </template>
                </form-item>
            </i-form>
            <div v-if="detail.application.status === 'DONE'">
                <divider>result</divider>
                <h2 style="text-align: right;" v-if="detail.application.result_file.should_have_file">
                    <i-button icon="md-cloud-download" type="success" size="small"
                        @click="downloadExportedContentAsCSV"
                        :disabled="detail.application.result_file.error">Download ({{ (detail.application.result_file.size / 1024 / 1024).toFixed(2) }}M)</i-button>
                </h2>
                <span style="color: #ed4014;" v-if="detail.application.result_file.error">({{ detail.application.result_file.error }})</span>    
            </div>
            <div>
                <h2>History</h2>
                <application-history :history="detail.application.history"></application-history>
            </div>
            <Row slot="footer" v-if="detail.can_decide || detail.can_cancel"
                type="flex" justify="space-around" class="code-row-bg">
                <Col span="5" v-if="detail.can_decide" style="text-align: center;">
                    <i-button type="success" @click="approveApplication">Approve</i-button>
                </Col>
                <Col span="5" v-if="detail.can_cancel" style="text-align: center;">
                    <i-button type="warn"  @click="cancelApplication">Cancel</i-button>
                </Col>
                <Col span="5" v-if="detail.can_decide" style="text-align: center;">
                    <i-button type="error"  @click="denyApplication">Deny</i-button>
                </Col>
            </Row>
        </layout-drawer>
    `,
    props: {
        applicationId: {
            type: [Number, String]
        }
    },
    data () {
        return {
            isLoading: false,
            detail: {
                application: {
                    sql: {
                        show_create_table: [],
                        show_create_function: [],
                        show_create_procedure: [],
                        show_create_trigger: []
                    },
                    applyUser: {},
                    database: {},
                    history: [],
                    result_file: [],
                    preview_table: []
                }
            },
            allUserMap: JSON.parse(localStorage.getItem('allUserMap'))
        };
    },
    methods: {
        init () {
            this.getApplicationDetail()
        },
        updateLoading (bool) {
            this.isLoading = bool;
        },
        getApplicationDetail () {
            this.updateLoading(true);

            ajax('getApplicationDetail', {
                application_id: this.applicationId
            }).then((res) => {
                res.application.history = res.application.history.map((item) => {
                    const user = this.allUserMap[item.actUser];

                    item.actUser = item.actUser === 0 ? user.realname : `${user.realname}(${user.username})`;

                    return item;
                });
                res.application.sql = JSON.parse(res.application.sql)

                this.detail = res
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            }).finally(() => {
                this.updateLoading(false);
            });
        },
        approveApplication () {
            ajax('approveApplication', {
                application_id: this.applicationId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Approve Application Success!', 2);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        cancelApplication () {
            ajax('cancelApplication', {
                application_id: this.applicationId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Cancel Application Success!', 2);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        denyApplication () {
            ajax('denyApplication', {
                application_id: this.applicationId,
                reason: prompt("Reason for your decision:"),
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Deny Application Success!', 2);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        downloadExportedContentAsCSV () {
            const api = API.downloadExportedContentAsCSV;
            const filename = this.detail.application.result_file.path.split('/').pop();

            let url = SinriQF.config.ApiBase + api.url + "?application_id=" + this.applicationId + "&token=" + SinriQF.api.getTokenFromCookie()
            console.log("downloadExportedContentAsCSV: ", url);
            window.location.href = (url);
        }
    }
});
