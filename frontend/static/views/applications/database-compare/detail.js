const DetailDatabaseCompareApplicationPage = {
    template: `
        <spin fix v-if="isLoading"></spin>
        <layout-drawer v-else>
            <div slot="header" style="display: flex;align-items: center;">
                <div style="flex: 1;overflow: hidden;margin-left: 10px;">
                    <h2 class="title">
                        Database Compare Application #{{ applicationId }}
                        <tag :color="detail.application.status | getApplicationStatusTagColor">{{ detail.application.status }}</tag>
                    </h2>
                    <h3 class="sub-title text-ellipsis" :title="detail.application.title">{{ detail.application.title }}</h3>
                </div>
                <div>Applied by {{detail.application.applyUser.realname}}({{detail.application.applyUser.username}})</div>
            </div>
            <div style="display: flex;padding: 10px;background-color: rgb(247, 247, 249);text-transform: uppercase;">
                <div style="flex: auto;"><strong style="margin-right: 5px;">Type:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.type }}</span></div>
                <div style="flex: auto;"><strong style="margin-right: 5px;">Create time:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.createTime }}</span></div>
            </div>
            <p style="margin: 10px 0;padding: 5px;">{{ detail.application.description }}</p>
            <i-form ref="form"
                style="padding: 20px 10px 10px;background-color: rgb(247, 247, 249);"
                :label-width="160"
                :model="detail.application.sql" 
            >
                <form-item label="Database A:">{{ detail.application.database.databaseName }}</form-item>
                <form-item label="Database B:">{{ detail.application.compare_database.databaseName }}</form-item>
                <form-item label="Schema:">{{ detail.application.sql.schema }}</form-item>
            </i-form>
            <div style="margin: 10px 0;padding: 5px;text-align: right" v-if="detail.can_decide || detail.can_cancel">
                <i-button type="success" v-if="detail.can_decide"
                    @click="approveApplication">Approve</i-button>
                <i-button type="error" v-if="detail.can_decide" 
                    @click="denyApplication">Deny</i-button>
                <i-button type="warn" v-if="detail.can_cancel"
                    @click="cancelApplication">Cancel</i-button>
            </div>            
            <div v-if="detail.application.status === 'DONE'">
                <divider>result</divider>
                <h2 style="text-align: right;" v-if="detail.application.result_file.should_have_file">
                    <i-button icon="md-cloud-download" type="success" size="small"
                        @click="downloadExportedContentAsCSV"
                        :disabled="detail.application.result_file.error">Download ({{ (detail.application.result_file.size / 1024 / 1024).toFixed(2) }}M)</i-button>
                </h2>
                <span style="color: #ed4014;" v-if="detail.application.result_file.error">({{ detail.application.result_file.error }})</span>    
            </div>
            <div slot="footer" >
                <h2>History</h2>
                <application-history :history="detail.application.history"></application-history>
            </div> 
        </layout-drawer>
    `,
    data () {
        return {
            applicationId: 0,
            isLoading: false,
            detail: {
                application: {
                    sql: {},
                    applyUser: {},
                    database: {},
                    compare_database: {},
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
            this.getApplicationDetail();
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
    },
    mounted () {
        this.applicationId = this.$route.query.applicationId;
        this.init();
    }
};
