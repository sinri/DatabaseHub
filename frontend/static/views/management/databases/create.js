const CreateDatabasePage = {
    template: `
        <layout>
            <h2 class="title">Create Database</h2>
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
                    <i-select clearable filterable v-model="form.model.status">
                        <i-option v-for="item in CONSTANTS.DATABASE_STATUS" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                </form-item>
                
                <form-item label="Engine" prop="engine">
                    <i-select clearable filterable v-model="form.model.engine">
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
                    status: 'NORMAL',
                    engine: 'MYSQL'
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
        };
    },
    methods: {
        back () {
            this.$router.replace({
                name: 'databaseListPage'
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

            ajax('createDatabase', {
                database_info: data
            }).then(() => {
                SinriQF.iview.showSuccessMessage('Create Database Success!', 2);
                this.back();
            }).catch(({msg}) => {
                SinriQF.iview.showErrorMessage(msg, 5);
            })
        }
    }
};
