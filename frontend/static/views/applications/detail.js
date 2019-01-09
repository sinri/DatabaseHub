const DetailApplicationPage = {
    template: `
        <spin fix v-if="isLoading"></spin>
        <layout-drawer v-else>
            <div slot="header" style="display: flex;align-items: center;">
                <div style="flex: 1;overflow: hidden;margin-left: 10px;">
                    <h2 class="title">
                        Application #{{ applicationId }}
                        <tag :color="detail.application.status | getApplicationStatusTagColor">{{ detail.application.status }}</tag>
                    </h2>
                    <h3 class="sub-title text-ellipsis" :title="detail.application.title">{{ detail.application.title }}</h3>
                </div>
                <avatar size="42" :username="detail.application.applyUser.username" :real-name="detail.application.applyUser.realname"></avatar>
            </div>
            <div style="display: flex;padding: 10px;background-color: rgb(247, 247, 249);text-transform: uppercase;">
                <div style="flex: auto;"><strong style="margin-right: 5px;">Type:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.type }}</span></div>
                <div style="flex: auto;"><strong style="margin-right: 5px;">Database:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.database.databaseName }}</span></div>
                <div style="flex: auto;"><strong style="margin-right: 5px;">Create time:</strong><span style="color: rgb(232, 62, 140);">{{ detail.application.createTime }}</span></div>
            </div>
            <p style="margin: 10px 0;padding: 5px;">{{ detail.application.description }}</p>
            <codemirror style="font-size: 14px;"
                        :options="codeMirrorOptions"
                        v-model="detail.application.sql"></codemirror>
            <div v-if="detail.application.status !== 'APPROVED'">
                <divider>result</divider>
                <h2 v-if="detail.application.result_file.should_have_file">预览（最多10条）
                    <i-button icon="md-cloud-download" type="success" size="small" style="float: right;"
                        @click="downloadExportedContentAsCSV"
                        :disabled="detail.application.result_file.error">下载({{ (detail.application.result_file.size / 1024 / 1024).toFixed(2) }}M)</i-button>
                </h2>
                <span style="color: #ed4014;" v-if="detail.application.result_file.error">({{ detail.application.result_file.error }})</span>    
                <native-table style="margin-bottom: 30px;border: 10px solid #ccc;"
                    :columns="previewTableColumns"
                    :data="detail.application.preview_table.slice(1)"
                    v-if="detail.application.result_file.should_have_file && !detail.application.result_file.error"></native-table>        
                
                <h2>History</h2>
                <native-table
                    :columns="historyTableColumns"
                    :data="detail.application.history.slice(0, 100)"></native-table>
            </div>
            <div slot="footer" v-if="detail.can_decide || detail.can_cancel || detail.can_edit">
                <i-button type="primary" v-if="detail.can_decide"
                    @click="approveApplication">Approve</i-button>
                <i-button type="primary" v-if="detail.can_decide" 
                    @click="denyApplication">Deny</i-button>
                <i-button v-if="detail.can_cancel"
                    @click="cancelApplication">Cancel</i-button>
                <i-button v-if="detail.can_edit"
                    @click="goEditApplicationPage">Edit</i-button>
            </div> 
        </layout-drawer>
    `,
    data () {
        return {
            applicationId: 0,
            isLoading: false,
            detail: {
                application: {
                    applyUser: {},
                    database: {},
                    history: [],
                    result_file: [],
                    preview_table: []
                }
            },
            codeMirrorOptions: {
                tabSize: 4,
                styleActiveLine: true,
                lineNumbers: true,
                line: true,
                mode: 'text/x-mysql',
                theme: 'panda-syntax'
            },
            allUserMap: JSON.parse(localStorage.getItem('allUserMap'))
        };
    },
    computed: {
        previewTableColumns () {
            const columns = [];

            if (this.detail.application.preview_table &&
                this.detail.application.preview_table.length > 0
            ) {
                this.detail.application.preview_table[0].forEach((key, index) => {
                    columns.push({
                        title: key,
                        key: index
                    });
                });
            }

            return columns;
        },
        historyTableColumns () {
            const columns = [];

            if (this.detail.application.history.length > 0) {
                Object.keys(this.detail.application.history[0]).forEach((key) => {
                    columns.push({
                        title: key,
                        key
                    });
                });
            }

            return columns;
        }
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
                application_id: this.applicationId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Deny Application Success!', 2);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        goEditApplicationPage () {
            const query = {
                application_id: this.detail.application.applicationId,
                title: this.detail.application.title,
                description: this.detail.application.description,
                database_id: this.detail.application.database.databaseId,
                type: this.detail.application.type,
                sql: this.detail.application.sql
            }

            this.$router.push({
                name: 'editApplicationPage',
                query
            })
        },
        downloadExportedContentAsCSV () {
            const api = API.downloadExportedContentAsCSV;
            const filename = this.detail.application.result_file.path.split('/').pop();

            axios.post(SinriQF.config.ApiBase + api.url, {
                application_id: this.applicationId,
                token: SinriQF.api.getTokenFromCookie()
            }).then(({data}) => {
                exportCsv.download(filename, data);
            });
        }
    },
    mounted () {
        this.applicationId = this.$route.query.applicationId;
        this.init();
    }
};
