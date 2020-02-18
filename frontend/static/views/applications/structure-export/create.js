window._cache_databaseStructure = {} // 缓存数据库结构信息
window._cache_databaseSchemas = {} // 缓存数据库Schema信息
const CreateStructureExportApplicationPage = {
    template: `
        <layout>
            <h2 class="title">Structure Export Application</h2>
            <divider></divider>
            <i-form ref="form" style="width: 90%;"
                :label-width="160"
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
                        
                <form-item label="Schema" prop="sql.schema">
                    <i-select clearable filterable @on-change="handleDatabaseSchemaChange" v-model="form.model.sql.schema" style="width:200px">
                        <i-option v-for="item in schemas" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                </form-item>
                
                <form-item label="Show Create Database" required>
                    <i-switch v-model="form.model.sql.show_create_database" />
                </form-item>

                <form-item label="Drop If Exist" required>
                    <i-switch v-model="form.model.sql.drop_if_exist" />
                </form-item>

                <form-item label="Reset Auto Increment" required>
                    <i-switch v-model="form.model.sql.reset_auto_increment" />
                </form-item>
                <form-item label="Options">
                    <Collapse v-model="type">
                        <Panel name="TABLES">
                            TABLES
                            <div slot="content">
                                <div style="margin-bottom: 8px;">
                                    <i-button  @click="handleSelectAll('show_create_table')">Select All</i-button> / <i-button @click="handleClear('show_create_table')">Clear</i-button>
                                </div>
                                <select size="10" multiple v-model="form.model.sql.show_create_table" style="padding: 5px;width: 200px;border-radius: 4px;border: 1px solid #dcdee2;">
                                    <option v-for="item in databaseStructure.show_create_table" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                        </Panel>
                        <Panel name="FUNCTIONS">
                            FUNCTIONS
                            <div slot="content">
                                <div style="margin-bottom: 8px;">
                                    <i-button  @click="handleSelectAll('show_create_function')">Select All</i-button> / <i-button @click="handleClear('show_create_function')">Clear</i-button>
                                </div>
                                <select size="10" multiple v-model="form.model.sql.show_create_function" style="padding: 5px;width: 200px;border-radius: 4px;border: 1px solid #dcdee2;">
                                    <option v-for="item in databaseStructure.show_create_function" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                        </Panel>
                        <Panel name="PROCEDURES">
                            PROCEDURES
                            <div slot="content">
                                <div style="margin-bottom: 8px;">
                                    <i-button  @click="handleSelectAll('show_create_procedure')">Select All</i-button> / <i-button @click="handleClear('show_create_procedure')">Clear</i-button>
                                </div>
                                <select size="10" multiple v-model="form.model.sql.show_create_procedure" style="padding: 5px;width: 200px;border-radius: 4px;border: 1px solid #dcdee2;">
                                    <option v-for="item in databaseStructure.show_create_procedure" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                        </Panel>
                        <Panel name="TRIGGERS">
                            TRIGGERS
                            <div slot="content">
                                <div style="margin-bottom: 8px;">
                                    <i-button  @click="handleSelectAll('show_create_trigger')">Select All</i-button> / <i-button @click="handleClear('show_create_trigger')">Clear</i-button>
                                </div>
                                <select size="10" multiple v-model="form.model.sql.show_create_trigger" style="padding: 5px;width: 200px;border-radius: 4px;border: 1px solid #dcdee2;">
                                    <option v-for="item in databaseStructure.show_create_trigger" :key="item" :value="item">{{ item }}</option>
                                </select>
                            </div>
                        </Panel>
                    </Collapse>
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
            type: 'TABLES',
            form: {
                model: {
                    title: '',
                    description: '',
                    database_id: '',
                    type: 'EXPORT_STRUCTURE',
                    sql: {
                        schema: '',
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
                    'sql.schema': [
                        {required: true, message: '不能为空'}
                    ]
                }
            },
            databaseStructure: {
                show_create_table: [],
                show_create_function: [],
                show_create_procedure: [],
                show_create_trigger: []
            },
            schemas: {},
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
        convertSqlArray (data, name) {
            if (data.sql[name].length !== 0 && data.sql[name].length === this.databaseStructure[name].length) {
                data.sql[name] = 'ALL'
            }

            return data
        },
        save () {
            const data = JSON.parse(JSON.stringify(this.form.model));

            this.convertSqlArray(data, 'show_create_table')
            this.convertSqlArray(data, 'show_create_function')
            this.convertSqlArray(data, 'show_create_procedure')
            this.convertSqlArray(data, 'show_create_trigger')

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
            this.form.model.sql = Object.assign(this.form.model.sql, {
                schema: '',
                show_create_table: '', // array 全部传字符串'ALL',空数组表示全不选
                show_create_function: [], //array 全部传字符串'ALL',空数组表示全不选
                show_create_procedure: [], //array 全部传字符串'ALL',空数组表示全不选
                show_create_trigger: [] //array 全部传字符串'ALL',空数组表示全不选
            })

            if (typeof database_id === 'undefined') return

            this.getDatabaseSchemas(database_id)
        },
        handleDatabaseSchemaChange (schema) {
            this.form.model.sql = Object.assign(this.form.model.sql, {
                show_create_table: '', // array 全部传字符串'ALL',空数组表示全不选
                show_create_function: [], //array 全部传字符串'ALL',空数组表示全不选
                show_create_procedure: [], //array 全部传字符串'ALL',空数组表示全不选
                show_create_trigger: [] //array 全部传字符串'ALL',空数组表示全不选
            })

            if (typeof schema === 'undefined') return

            this.getDatabaseStructure({
                database_id: this.form.model.database_id,
                schema
            })
        },
        handleSelectAll (name) {
            this.form.model.sql[name] = this.databaseStructure[name]
        },
        handleClear (name) {
            this.form.model.sql[name] = []
        },
        getDatabaseSchemas (database_id) {
            const schemas = window._cache_databaseSchemas[database_id]

            if (typeof schemas !== 'undefined') {
                this.schemas = schemas

                return
            }

            ajax('getDatabaseStructure', {database_id}).then(({result}) => {
                const {
                    schemas
                } = result
                
                this.schemas = schemas
                window._cache_databaseSchemas[database_id] = schemas
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        getDatabaseStructure ({database_id, schema}) {
            const databaseSchema = `${database_id}_${schema}`
            const structure = window._cache_databaseStructure[databaseSchema]

            if (typeof structure !== 'undefined') {
                this.databaseStructure = structure

                return
            }

            ajax('getDatabaseStructure', {database_id, schema}).then(({result}) => {
                const {
                    schemas,
                    tables: show_create_table,
                    functions: show_create_function,
                    procedures: show_create_procedure,
                    triggers: show_create_trigger
                } = result
                const structure = {
                    schemas,
                    show_create_table,
                    show_create_function,
                    show_create_procedure,
                    show_create_trigger
                }
                
                this.databaseStructure = structure
                window._cache_databaseStructure[databaseSchema] = structure
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
