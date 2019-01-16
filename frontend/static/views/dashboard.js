const DashboardPage = {
    template: `
        <Row>
            <i-col span="22" style="margin: 10px">
                <div class="markdown-body" v-html="mdHtml"></div>
            </i-col>
        </Row>
    `,
    data () {
        return {
            mdHtml: '',
            mdText: `## Welcome!`
        }
    },
    mounted () {
        const md = window.markdownit();

        this.mdHtml = md.render(this.mdText);

        ajax('dashboardMeta', {}).then(({doc}) => {
            console.log('dashboardMeta.doc', doc);
            this.mdText = doc;
            this.mdHtml = md.render(this.mdText);
        }).catch(({message}) => {
            SinriQF.iview.showErrorMessage(message, 5);
        });


    }
};
