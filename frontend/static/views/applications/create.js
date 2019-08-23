const CreateApplicationPage = {
    template: `
        <layout>
            <h2 class="title">Create Application</h2>
            <divider></divider>
            <i-form ref="form" style="width: 90%;"
                :label-width="120"
                :model="form.model" 
                :rules="form.rules"
                >
                <form-item label="Title" prop="title">
                    <i-input clearable v-model.trim="form.model.title" style="width:400px" />
                </form-item>
                
                <form-item label="Description" prop="description">
                    <i-input clearable type="textarea" v-model.trim="form.model.description" />
                </form-item>
                
                <form-item label="Database" prop="database_id">
                    <i-select clearable filterable v-model="form.model.database_id" style="width:200px">
                        <i-option v-for="item in databaseList" 
                            :key="item.databaseId" 
                            :value="item.databaseId">{{ item.databaseName }} ({{ item.engine }})</i-option>
                    </i-select>
                </form-item>
                
                <form-item label="Type" prop="type">
                    <i-select clearable filterable v-model="form.model.type" style="width:200px">
                        <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                </form-item>
                
                <form-item label="SQL" prop="sql">
                    <codemirror style="font-size: 14px;"
                        :options="codeMirrorOptions"
                        v-model.trim="form.model.sql"></codemirror>
                </form-item>
                                
                <form-item>
                    <Row>
                        <i-col span="12"><i-button @click="back">Back</i-button></i-col>
                        <i-col span="12" style="text-align: right"><i-button type="primary" @click="onSubmit">Submit</i-button></i-col>
                    </Row>
                </form-item>
            </i-form>
        </layout>
    `,
    data () {
        return {
            form: {
                model: {
                    title: '',
                    description: '',
                    database_id: '',
                    type: '',
                    sql: ''
                },
                rules: {
                    title: [
                        {required: true, message: '不能为空'}
                    ],
                    description: [
                        {required: true, message: '不能为空'}
                    ],
                    database_id: [
                        {required: true, message: '不能为空'}
                    ],
                    type: [
                        {required: true, message: '不能为空'}
                    ],
                    sql: [
                        {required: true, message: '不能为空'}
                    ]
                }
            },
            databaseList: [],
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
        back () {
            this.$router.replace({
                name: 'applicationListPage'
            });
        },
        onSubmit () {
           this.$refs.form.validate((valid) => {
               if (valid) {
                   this.save();
               }
           });
        },
        save () {
            const data = JSON.parse(JSON.stringify(this.form.model));

            ajax('createApplication', {
                application: data
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Create Application Success!', 2);
                this.back();
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            })
        },
        getDatabaseList () {
            ajax('commonDatabaseList').then(({list}) => {
                this.databaseList = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getDatabaseList()
    }
};
