Vue.component('native-table-cell-render', {
    name: 'RenderCell',
    functional: true,
    props: {
        row: Object,
        render: Function,
        index: Number,
        column: {
            type: Object,
            default: null
        }
    },
    render: (h, ctx) => {
        const params = {
            row: ctx.props.row,
            index: ctx.props.index
        }

        if (ctx.props.columns) params.column = ctx.props.column

        return ctx.props.render(h, params)
    }
});
