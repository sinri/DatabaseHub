const ApprovalsPage = {
    template: `
    <i-table border :columns="myApprovalsTable.columns" :data="myApprovalsTable.data"></i-table>
    `,
    data () {
        return {
            query: {},
            myApprovalsTable: {
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
                data: []
            }
        }
    },
    methods: {
        search () {
            ajax('myApprovals').then(({list}) => {
                this.myApprovalsTable.data = list
            })
        }
    },
    mounted () {
        this.search()
    }
};