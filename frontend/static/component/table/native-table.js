Vue.component('native-table', {
    template: `
    <div style="position: relative;overflow: auto;">
        <table class="c-native-table">
            <thead>
                <tr>
                    <th v-for="col in columns"
                        :key="col.key">
                        <span>{{ col.title }}</span>
                        <span class="c-native-table-sort" v-if="col.sortable">
                            <Icon type="md-arrow-dropup"
                                :class="{on: currentSort.key === col.key && currentSort.type === 1}"
                                @click="sortAsc(col)"></Icon>
                            <Icon type="md-arrow-dropdown"
                                :class="{on: currentSort.key === col.key && currentSort.type === -1}"
                                @click="sortDesc(col)"></Icon>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="c-native-table-tips" v-if="sortedData.length === 0">
                    <td :colspan="columns.length" style="text-align: center;">N/A</td>
                </tr>
                <tr v-for="(row, rowIndex) in sortedData" :key="rowIndex">
                    <td v-for="(col, colIndex) in columns" :key="col.key" style="padding: 2px">
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
    },
    computed: {
        sortedData () {
            const data = [...this.data]
            const {key, type, sortMethod} = this.currentSort

            // no sort
            if (typeof key === 'undefined' || type === 0) {
                return data
            }

            // if not custom sort method
            if (typeof sortMethod === 'undefined') {
                return data.sort((from, to) => {
                    return type * (from[key] - to[key])
                })
            }

            // use custom sort method
            return data.sort((from, to) => {
                return sortMethod(from, to, type === 1 ? 'asc' : 'desc')
            })
        }
    },
    data () {
        return {
            currentSort: {}
        }
    },
    methods: {
        sortAsc ({key, sortMethod}) {
            if (this.currentSort.key === key && this.currentSort.type === 1) {
                this.currentSort = {
                    key,
                    type: 0,
                    sortMethod
                }
            } else {
                this.currentSort = {
                    key,
                    type: 1,
                    sortMethod
                }
            }
        },
        sortDesc ({key, sortMethod}) {
            if (this.currentSort.key === key && this.currentSort.type === -1) {
                this.currentSort = {
                    key,
                    type: 0,
                    sortMethod
                }
            } else {
                this.currentSort = {
                    key,
                    type: -1,
                    sortMethod
                }
            }
        }
    }
});
