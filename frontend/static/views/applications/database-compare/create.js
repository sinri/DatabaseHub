window._cache_databaseStructure = {} // 缓存数据库结构信息
window._cache_databaseSchemas = {} // 缓存数据库Schema信息
const CreateDatabaseCompareApplicationPage = {
    template: `
        <layout>
            <h2 class="title">Database Compare Application</h2>
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
                
                <form-item label="Database A" prop="database_id">
                    <i-select clearable filterable @on-change="handleDatabaseAChange" v-model="form.model.database_id" style="width:200px">
                        <i-option v-for="item in databaseList" 
                            :key="item.databaseId"
                            :disabled="item.databaseId === form.model.sql.compare_database_id"
                            :value="item.databaseId">{{ item.databaseName }} ({{ item.engine }})</i-option>
                    </i-select>
                </form-item>

                <form-item label="Database B" prop="sql.compare_database_id">
                    <i-select clearable filterable @on-change="handleDatabaseBChange" v-model="form.model.sql.compare_database_id" style="width:200px">
                        <i-option v-for="item in databaseList" 
                            :key="item.databaseId"
                            :disabled="item.databaseId === form.model.database_id"
                            :value="item.databaseId">{{ item.databaseName }} ({{ item.engine }})</i-option>
                    </i-select>
                </form-item>

                <form-item label="Schema" prop="sql.schema">
                    <i-select clearable filterable v-model="form.model.sql.schema" style="width:200px">
                        <i-option v-for="item in schemas.intersection" 
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
                    type: 'DATABASE_COMPARE',
                    sql: {
                        schema: '',
                        compare_database_id: ''
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
                    ],
                    'sql.compare_database_id': [
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
            schemas: {
                a: {},
                b: {},
                intersection: {}
            },
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
        resetAfterDatabaseChange (name) {
            this.form.model.sql.schema = '',
            this.schemas[name] = {}
            this.schemas.intersection = {}
        },
        handleDatabaseChange (database_id, name) {
            this.resetAfterDatabaseChange(name)

            if (typeof database_id === 'undefined') return

            this.getDatabaseSchemas(database_id, name)
        },
        handleDatabaseAChange (database_id) {
            this.handleDatabaseChange(database_id, 'a')
        },
        handleDatabaseBChange (database_id) {
            this.handleDatabaseChange(database_id, 'b')
        },
        getDatabaseSchemaIntersection  () {
            const schemas = {}
            const {a: schemasA, b: schemasB} = this.schemas

            for (const key in schemasA) {
                if (schemasA[key] === schemasB[key]) {
                    schemas[key] = schemasA[key]
                }
            }

            this.schemas.intersection = schemas
        },
        updateSchemas(schemas, name) {
            this.schemas[name] = schemas
            this.$nextTick(() => {
                this.getDatabaseSchemaIntersection()
            })
        },
        getDatabaseSchemas (database_id, name) {
            const schemas = window._cache_databaseSchemas[database_id]

            if (typeof schemas !== 'undefined') {
                this.updateSchemas(schemas, name)

                return
            }

            ajax('getDatabaseStructure', {database_id}).then(({result}) => {
                const {
                    schemas
                } = result
                
                this.updateSchemas(schemas, name)
                window._cache_databaseSchemas[database_id] = schemas
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted () {
        this.getDatabaseList()
    },
    beforeDestroy () {
        window._cache_databaseStructure = {}
        window._cache_databaseSchemas = {}
    }
};
