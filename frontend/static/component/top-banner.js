Vue.component('top-banner', {
    template: `
        <i-menu mode="horizontal" theme="dark"
            :active-name="activeMenuName"
            @on-select="onMenuItemSelected">
            <template v-for="item in menuItems">
                <menu-item
                    :key="item.name"
                    :name="item.name"
                    :style="item.style + (item.name === activeMenuName ? 'background: rgb(91, 100, 120);' : '')">
                    <Icon :type="item.icon" /> {{item.text}}
                </menu-item>
            </template>
        </i-menu>
    `,
    data () {
        return {
            activeMenuName: 'indexPage',
            menuItems: [
                {
                    name: 'indexPage',
                    style: 'width: 30%;text-align: left;',
                    icon: 'ios-nuclear',
                    text: 'DatabaseHub'
                },
                {
                    name: 'approvalsPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Approvals'
                },
                {
                    name: 'taskPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Tasks'
                },
                {
                    name: 'configPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Configure'
                },
                {
                    name: 'queryPage',
                    style: 'width: 10%;text-align: center;',
                    icon: 'ios-nuclear',
                    text: 'Quick Query'
                },
                {
                    name: 'userInfo',
                    style: 'width: 20%;text-align: right;',
                    icon: 'ios-people',
                    text: JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).realname
                },
                {
                    name: 'logout',
                    style: 'width: 10%;text-align: right;',
                    icon: 'ios-exit-outline',
                    text: 'Logout'
                }
            ]
        };
    },
    methods: {
        onMenuItemSelected (name) {
            this.activeMenuName = name;

            switch (name) {
                case 'userInfo':
                    break;
                case 'logout':
                    SinriQF.cookies.cleanCookie('DatabaseHubUser');
                    SinriQF.cookies.cleanCookie(SinriQF.config.TokenName);
                    window.location.href = 'login.html';

                    break;
                default:
                    router.push({name});
            }
        }
    },
    mounted () {
        this.activeMenuName = router.currentRoute.name || 'indexPage'
    }
});
