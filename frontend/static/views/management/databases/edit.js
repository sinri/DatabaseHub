const EditDatabasePage = {
    template: `
        <layout>
            <h2 class="title">Edit Database</h2>
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
                    <i-button type="primary" @click="onSubmit">Submit</i-button>
                    <i-button @click="back">Back</i-button>
                </form-item>
            </i-form>
        </layout>
    `,
    data () {
        return {
            form: {
                model: {
                    database_name: '',
                    host: '',
                    port: '',
                    status: '',
                    engine: ''
                },
                rules: {
                    database_name: [
                        {required: true, message: '不能为空'}
                    ],
                    host: [
                        {required: true, message: '不能为空'}
                    ],
                    port: [
                        {required: true, message: '不能为空'}
                    ],
                    status: [
                        {required: true, message: '不能为空'}
                    ],
                    engine: [
                        {required: true, message: '不能为空'}
                    ]
                }
            }
        }
    },
    methods: {
        back () {
            this.$router.replace({
                name: 'databaseListPage'
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

            ajax('editDatabase', {
                database_id: data.database_id,
                database_info: data
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Edit Database Success!', 2)
                this.back()
            }).catch(({msg}) => {
                SinriQF.iview.showErrorMessage(msg, 5);
            })
        }
    },
    created () {
        const {
            databaseId: database_id,
            databaseName: database_name,
            host,
            port,
            status,
            engine
        } = this.$route.query


        Object.assign(this.form.model, {
            database_id,
            database_name,
            host,
            port,
            status,
            engine
        })
    }
};
