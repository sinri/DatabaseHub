const ApplicationListPage = {
    template: `
        <layout-list>
            <i-form slot="search" inline>
                 <form-item>
                     <i-input type="text" placeholder="Title" />
                 </form-item>
                 <form-item>
                     <i-input type="text" placeholder="Database ID" />
                 </form-item>
                 <form-item>
                     <i-select>
                        <i-option v-for="item in CONSTANTS.APPLICATION_TYPES" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                 </form-item>
                 <form-item>
                     <i-input type="text" placeholder="Apply User" />
                 </form-item>
                 <form-item>
                    <i-select>
                        <i-option v-for="item in CONSTANTS.APPLICATION_STATUS" 
                            :key="item" 
                            :value="item">{{ item }}</i-option>
                    </i-select>
                 </form-item>
            </i-form>
            </div>
            <div slot="handle">
                <i-button type="primary" @click="search">Search</i-button>
                <i-button type="primary" @click="goCreateApplication">Create Application</i-button>
            </div>
            
            <i-table border :columns="applicationTable.columns" :data="applicationTable.data"></i-table>
            
            <page slot="pagination" :total="applicationTable.total" />
        </layout-list>
    `,
    data () {
        return {
            query: {
                title: '',
                database_id: '',
                type: [],
                apply_user: '',
                status: [],
                page: 1,
                page_size: 10
            },
            applicationTable: {
                columns: [
                    {
                        title: 'Application ID',
                        key: ''
                    },
                    {
                        title: 'Title',
                        key: ''
                    },
                    {
                        title: 'Database',
                        key: ''
                    },
                    {
                        title: 'Type',
                        key: ''
                    },
                    {
                        title: 'Status',
                        key: ''
                    },
                    {
                        title: 'Applicant',
                        key: ''
                    },
                    {
                        title: 'Applied Time'
                    },
                    {
                        title: 'Action'
                    }
                ],
                data: [],
                total: 0
            }
        };
    },
    methods: {
        search () {
            const query = JSON.parse(JSON.stringify(this.query));

            ajax('searchApplication', query).then(({list, total}) => {
                this.applicationTable.data = list;
                this.applicationTable.total = list;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        },
        goCreateApplication () {
            this.$router.push({
                name: 'createApplicationPage'
            });
        },
        goEditApplication (item) {
            const query = JSON.parse(JSON.stringify(item));

            this.$router.push({
                name: 'editApplicationPage',
                query
            });
        }
    },
    mounted () {
        this.search();
    }
};
