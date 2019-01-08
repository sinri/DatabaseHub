const DashboardPage = {
    template: `
        <Row>
            <i-col span="12" style="margin: 10px">
                <div class="markdown-body" v-html="mdHtml"></div>
            </i-col>
        </Row>
    `,
    data () {
        return {
            mdHtml: '',
            mdText: `
## 欢迎使用DBREQ数据中心 Version 3.0

> 请使用Account Auth系统的登陆账户和密码使用该系统。

如果尚未在本系统注册，可通过他人在本系统发起注册申请。
Version 3.0 支持一下数据申请的递交、审批和执行：
- 数据导出：执行单条Select语句导出CSV数据；
- 数据更新：执行多条更新(Update/Insert/Delete/Replace)。
- 结构变更：执行DDL语句。
- 特别注意
- 每次申请SQL总长度不超过224字节，包括可见和不可见字符。

## 特别注意

申请结构变更时，需要新建和变更表、函数或存储过程，不要忘记必要的 \`DROP IF EXISTS\` 。

如新建函数和存储过程等，不需要声明和恢复 \`DELIMITER\` 语句，在 \`BEGIN\` 和 \`END\` 之间正常使用;即可。

姑且将 \`CALL\` 类型的也归于结构类请求，每次一条，不保证返回值。

有人喜欢用 \`truncate\`，于是就打了补丁。关键字小写，与表名之间以空格分隔，库和表名不能加\`。

现在explain也可以当select使了。
`
        }
    },
    mounted () {
        const md = window.markdownit();

        this.mdHtml = md.render(this.mdText);
        console.log(this.mdHtml)
    }
};
