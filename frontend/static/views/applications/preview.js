Vue.component('application-preview', {
    template: `
        <spin fix v-if="isLoading"></spin>
        <layout-drawer v-else>
            <div slot="header" style="display: flex;align-items: center;">
                <avatar size="42" :username="detail.application.applyUser.username" :real-name="detail.application.applyUser.realname"></avatar>
                <div style="flex: 1;overflow: hidden;margin-left: 10px;">
                    <h2 class="title">
                        Application #{{ applicationId }}
                        <tag :color="detail.application.status | getApplicationStatusTagColor">{{ detail.application.status }}</tag>
                    </h2>
                    <h3 class="sub-title text-ellipsis" :title="detail.application.title">{{ detail.application.title }}</h3>
                </div>
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
            <divider>result</divider>
            <div>
                
            </div>
            <div slot="footer" v-if="detail.can_decide || detail.can_cancel || detail.can_edit">
                <i-button type="primary" v-if="detail.can_decide"
                    @click="approveApplication">Approve</i-button>
                <i-button type="primary" v-if="detail.can_decide" 
                    @click="denyApplication">Deny</i-button>
                <i-button v-if="detail.can_cancel"
                    @click="cancelApplication">Cancel</i-button>
                <i-button v-if="detail.can_edit"
                    @click="cancelApplication">Edit</i-button>
            </div> 
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
            }
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

            ajax('detailApplication', {
                application_id: this.applicationId
            }).then((res) => {
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
                SinriQF.iview.showSuccessMessage('Approve Application Success!', 3);
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
                SinriQF.iview.showSuccessMessage('Cancel Application Success!', 3);
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
                SinriQF.iview.showSuccessMessage('Deny Application Success!', 3);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    }
});
