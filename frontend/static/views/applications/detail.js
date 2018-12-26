Vue.component('application-detail', {
    template: `
        <layout-drawer>
            <h2 slot="header" class="title">
                Application #{{ applicationId }}
                <tag :color="detail.application.status | getApplicationStatusTagColor">{{ detail.application.status }}</tag>
            </h2>
            <h3 style="margin-bottom: 20px;padding: 10px 0;border-bottom: 1px solid #e8eaec">{{ detail.application.title }}</h3>
            <div style="display: flex;">
                <span style="flex: 1;">Type: {{ detail.application.type }}</span>
                <span style="flex: 1;">Database: {{ detail.application.database.databaseName }}</span>
                <span style="flex: 1;">CreateTime: {{ detail.application.createTime }}</span>
            </div>
            <p style="margin: 10px 0;padding: 5px;border-left: 2px solid #e8eaec;background-color: #f2f2f2;">{{ detail.application.description }}</p>
            <codemirror style="font-size: 14px;"
                        :options="codeMirrorOptions"
                        v-model="detail.application.sql"></codemirror>
            <divider dashed></divider>
            <div>
                result
            </div>
            <div slot="footer">
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
        getApplicationDetail () {
            ajax('detailApplication', {
                application_id: this.applicationId
            }).then((res) => {
                this.detail = res
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        approveApplication () {
            ajax('approveApplication', {
                application_id: this.applicationId
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Cancel Application Success!', 3);
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
                SinriQF.iview.showSuccessMessage('Cancel Application Success!', 3);
                this.$emit('update')
                this.getApplicationDetail();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    }
});
