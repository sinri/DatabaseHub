const WorkersPage = {
    template: `<div>
            <p v-if="!output || output.length===0">No Workers Found.</p>
            <ul>
                <li v-for="line in output">{{ line }}</li>
            </ul>
        </div>`,
    data: function () {
        return {
            output: [],
        }
    },
    methods: {
        refreshWorkerList: function () {
            ajax("checkWorkerStatus", {type: 'json'}).then(({output}) => {
                console.log("output", output);
                this.output = output;
            }).catch(({message}) => {
                SinriQF.iview.showErrorMessage(message, 5);
            });
        }
    },
    mounted() {
        this.refreshWorkerList();
    }
};