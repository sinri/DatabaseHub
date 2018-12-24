Vue.component('application-detail', {
    template: `
        <layout>
            <h2 class="title">Application #{{ data.applicationId }}</h2>
            <divider></divider>
            <h4>{{ data.title }}</h4>
            <p>{{ data.description }}</p>
            <codemirror style="font-size: 14px;"
                        :options="codeMirrorOptions"
                        v-model="data.sql"></codemirror>
        </layout>
    `,
    props: {
        data: {
            type: Object,
            required: true
        }
    },
    data () {
        return {
            codeMirrorOptions: {
                tabSize: 4,
                styleActiveLine: true,
                lineNumbers: true,
                line: true,
                mode: 'text/x-mysql',
                theme: 'panda-syntax'
            }
        };
    }
});
