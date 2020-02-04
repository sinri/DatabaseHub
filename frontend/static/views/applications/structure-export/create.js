const CreateStructureExportApplicationPage = {
    template: `
        <layout>
            <h2 class="title">Structure Export Application</h2>
            <divider></divider>
            <Tabs v-model="type">
                <TabPane label="TABLES" name="TABLES">
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

                        <form-item label="show_create_database" props="sql.show_create_database">
                            <Switch v-model="form.model.sql.show_create_database" />
                        </form-item>

                        <form-item label="drop_if_exist" props="sql.drop_if_exist">
                            <Switch v-model="form.model.sql.drop_if_exist" />
                        </form-item>

                        <form-item label="reset_auto_increment" props="sql.reset_auto_increment">
                            <Switch v-model="form.model.sql.reset_auto_increment" />
                        </form-item>
                        
                        <form-item label="Tables" prop="sql.show_create_table">
                            <Transfer></Transfer>
                        </form-item>

                        <form-item label="Create Function" prop="sql.show_create_function">
                            <i-select clearable filterable v-model="form.model.type" style="width:200px">
                                <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                                    :key="item" 
                                    :value="item">{{ item }}</i-option>
                            </i-select>
                        </form-item>

                        <form-item label="Create Procedure" prop="sql.show_create_procedure">
                            <i-select clearable filterable v-model="form.model.type" style="width:200px">
                                <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                                    :key="item" 
                                    :value="item">{{ item }}</i-option>
                            </i-select>
                        </form-item>

                        <form-item label="Create Trigger" prop="sql.show_create_trigger">
                            <i-select clearable filterable v-model="form.model.type" style="width:200px">
                                <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                                    :key="item" 
                                    :value="item">{{ item }}</i-option>
                            </i-select>
                        </form-item>

                        <form-item>
                            <Row>
                                <i-col span="12"><i-button @click="back">Back</i-button></i-col>
                                <i-col span="12" style="text-align: right"><i-button type="primary" @click="onSubmit">Submit</i-button></i-col>
                            </Row>
                        </form-item>
                    </i-form>
                </TabPane>
                <TabPane label="FUNCTIONS" name="FUNCTIONS">FUNCTIONS</TabPane>
                <TabPane label="PROCEDURES" name="PROCEDURES">PROCEDURES</TabPane>
                <TabPane label="TRIGGERS" name="TRIGGERS">TRIGGERS</TabPane>
            </Tabs>
        </layout>
    `,
    data () {
        return {
            type: 'TABLES',
            form: {
                model: {
                    title: '',
                    description: '',
                    database_id: '',
                    type: '',
                    sql: {
                        show_create_database: '', // bool
                        drop_if_exist: '', // bool
                        reset_auto_increment: '', // bool
                        show_create_table: '', // array 全部传字符串'ALL',空数组表示全不选
                        show_create_function: '', // array 全部传字符串'ALL',空数组表示全不选
                        show_create_procedure: '', // array 全部传字符串'ALL',空数组表示全不选
                        show_create_trigger: '', // array 全部传字符串'ALL',空数组表示全不选
                    }
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
            databaseStructureCache: {}, // 缓存数据库结构信息
            databaseList: []
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

            data.sql = JSON.stringify(data.sql)

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
        },
        getDatabaseStructure (database_id) {
            const databaseStructure = this.databaseStructureCache[database_id]

            if (typeof databaseStructure !== 'undefined') return databaseStructure

            ajax('getDatabaseStructure', {database_id}).then(({result}) => {
                this.databaseStructureCache[database_id] = result
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getDatabaseList()
    }
};
