const ApprovalsPage = {
    template: `
        <i-table border :columns="myApprovals.columns" :data="myApprovals.data"></i-table>
    `,
    data () {
        return {
            query: {},
            myApprovals: {
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
            SinriQF.api.call(
                API.myApprovals,
                this.login_info,
                ({list}) => {
                    this.myApprovals.data = list
                },
                (error, status) => {
                    SinriQF.iview.showErrorMessage('Login Error. Feedback: ' + error + ' Status: ' + status, 5);
                }
            )
        }
    },
    mounted () {
        this.search()
    }
};
