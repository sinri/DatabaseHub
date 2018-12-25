const EditApplicationPage = {
    template: `
        <layout>
            <h2 class="title">Edit Application</h2>
            <divider></divider>
            <i-form ref="form" style="width: 640px;"
                :label-width="120"
                :model="form.model" 
                :rules="form.rules"
                >
                <form-item label="Name" prop="database_name">
                    <i-input clearable v-model.trim="form.model.database_name" />
                </form-item>
                
                <form-item label="Host" prop="host">
                    <i-input clearable v-model.trim="form.model.host" />
                </form-item>
                
                <form-item label="Port" prop="port">
                    <i-input clearable v-model.trim="form.model.port" />
                </form-item>
                
                <form-item label="Status" prop="status">
                    <i-select v-model="form.model.status">
                        <i-option v-for="item in CONSTANTS.DATABASE_STATUS" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                </form-item>
                
                <form-item label="Engine" prop="engine">
                    <i-select disabled v-model="form.model.engine">
                        <i-option v-for="item in CONSTANTS.DATABASE_TYPE" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                </form-item>
                
                <form-item>
                    <i-button type="primary" @click="onSubmit">保存</i-button>
                    <i-button @click="back">返回</i-button>
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
            }
        }
    },
    methods: {
        back () {
            this.$router.replace({
                name: 'applicationListPage'
            })
        },
        onSubmit () {
           this.$refs.form.validate((valid) => {
               if (valid) {
                   this.save()
               }
           })
        },
        save () {
            const data = JSON.parse(JSON.stringify(this.form.model))

            ajax('editApplication', {
                application_id: data.application_id,
                application: data
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Edit Application Success!', 3)
                this.back()
            }).catch(({msg}) => {
                SinriQF.iview.showErrorMessage(msg, 5);
            })
        }
    },
    created () {
        const query = JSON.parse(JSON.stringify(this.$route.query))

        Object.assign(this.form.model, query)
    }
};
