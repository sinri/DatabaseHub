window._cache_databaseStructure = {} // 缓存数据库结构信息
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
                            <i-select clearable filterable @on-change="handleDatabaseChange" v-model="form.model.database_id" style="width:200px">
                                <i-option v-for="item in databaseList" 
                                    :key="item.databaseId" 
                                    :value="item.databaseId">{{ item.databaseName }} ({{ item.engine }})</i-option>
                            </i-select>
                        </form-item>
                        
                        <form-item label="Show Create Database" props="sql.show_create_database">
                            <i-switch v-model="form.model.sql.show_create_database" />
                        </form-item>

                        <form-item label="Drop If Exist" props="sql.drop_if_exist">
                            <i-switch v-model="form.model.sql.drop_if_exist" />
                        </form-item>

                        <form-item label="Reset Auto Increment" props="sql.reset_auto_increment">
                            <i-switch v-model="form.model.sql.reset_auto_increment" />
                        </form-item>
                        
                        <form-item label="Tables" prop="sql.show_create_table">
                            <div style="margin-bottom: 8px;">
                                <i-button  @click="handleSelectAllTables">Select All</i-button> / <i-button @click="handleClearTables">Clear</i-button>
                            </div>
                            <select size="10" multiple v-model="form.model.sql.show_create_table" style="padding: 5px;width: 200px;border-radius: 4px;border: 1px solid #dcdee2;">
                                <option v-for="item in databaseStructure.tables" :key="item" :value="item">{{ item }}</option>
                            </select>
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
                    type: 'EXPORT_STRUCTURE',
                    sql: {
                        show_create_database: true, // bool
                        drop_if_exist: false, // bool
                        reset_auto_increment: true, // bool
                        show_create_table: '', // array 全部传字符串'ALL',空数组表示全不选
                        show_create_function: [], //array 全部传字符串'ALL',空数组表示全不选
                        show_create_procedure: [], //array 全部传字符串'ALL',空数组表示全不选
                        show_create_trigger: [] //array 全部传字符串'ALL',空数组表示全不选
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
            databaseStructure: {
                tables: [],
                functions: [],
                procedures: [],
                triggers: []
            },
            databaseList: []
        };
    },
    computed: {
        
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
        handleDatabaseChange (database_id) {
            if (typeof database_id === 'undefined') return

            this.getDatabaseStructure(database_id)
        },
        handleSelectAllTables () {
            this.form.model.sql.show_create_table = this.databaseStructure.tables
        },
        handleClearTables () {
            this.form.model.sql.show_create_table = []
        },
        setStructure (databaseStructure) {
            const {functions = [], procedures = [], triggers =[]} = databaseStructure

            this.databaseStructure = databaseStructure
            this.form.model.sql.show_create_function = functions
            this.form.model.sql.show_create_procedure = procedures
            this.form.model.sql.show_create_trigger = triggers
        },
        getDatabaseStructure (database_id) {
            const databaseStructure = window._cache_databaseStructure[database_id]

            if (typeof databaseStructure !== 'undefined') {
                this.setStructure(databaseStructure)
                return
            }

            ajax('getDatabaseStructure', {database_id}).then(({result}) => {
                this.setStructure(result)
                window._cache_databaseStructure[database_id] = result
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        window._cache_databaseStructure = {}
        this.getDatabaseList()
    }
};
