Vue.component('native-table', {
    template: `
    <div style="position: relative;">
        <table class="c-native-table">
            <thead>
                <tr>
                    <th v-for="col in columns"
                        :key="col.key">{{ col.title }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="data.length === 0">
                    <td :colspan="columns.length" style="text-align: center;">暂无数据</td>
                </tr>
                <tr v-for="(row, rowIndex) in data"
                    :key="rowIndex">
                    <td v-for="(col, colIndex) in columns"
                        :key="col.key">
                        <template v-if="typeof col.render === 'function'">
                        <native-table-cell-render
                            :row="row"
                            :column="col"
                            :render="col.render"
                            :index="colIndex"></native-table-cell-render>
                        </template>
                        <template v-else>{{ row[col.key] }}</template>
                    </td>
                </tr>
            </tbody>
        </table>
        <spin fix size="large" v-if="loading"></spin>
    </div>
    
    `,
    props: {
        columns: {
            type: Array,
            required: true
        },
        data: {
            type: Array,
            required: true
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
});
