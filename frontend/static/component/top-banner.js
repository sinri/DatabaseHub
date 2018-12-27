Vue.component('top-banner', {
    template: `
        <i-menu mode="horizontal" theme="dark"
            :active-name="activeMenuName"
            @on-select="onMenuItemSelected">
            <template v-for="item in menuItems">
                <submenu v-if="item.children"
                    :key="item.name"
                    :name="item.name">
                    <template slot="title">
                        <Icon :type="item.icon" v-if="item.icon" /> {{ item.text }}
                    </template>
                    <menu-item v-for="subItem in item.children"
                        :key="subItem.name"
                        :name="subItem.name"
                        :style="subItem.style">
                        <Icon :type="subItem.icon" v-if="subItem.icon" /> {{ subItem.text }}
                    </menu-item>
                </submenu>
                <menu-item v-else
                    :key="item.name"
                    :name="item.name"
                    :style="item.style">
                    <Icon :type="item.icon" v-if="item.icon" /> {{ item.text }}
                </menu-item>
            </template>
        </i-menu>
    `,
    data () {
        return {
            activeMenuName: 'DatabaseHub',
            menuItems: [
                {
                    name: 'dashboardPage',
                    style: 'width: 20%;text-align: left;',
                    icon: 'ios-nuclear',
                    text: 'DatabaseHub'
                },
                {
                    name: 'applicationListPage',
                    icon: 'ios-beaker',
                    text: 'Applications'
                },
                {
                    name: 'approvalListPage',
                    icon: 'md-nutrition',
                    text: 'Approvals'
                },
                {
                    name: 'managementPage',
                    icon: 'ios-flask',
                    text: 'Management',
                    children: [
                        {
                            name: 'databaseListPage',
                            icon: 'ios-cube',
                            text: 'Databases'
                        }
                    ]
                },
                {
                    name: 'configPage',
                    icon: 'ios-nuclear',
                    text: 'Configure'
                },
                {
                    name: 'queryPage',
                    icon: 'ios-nuclear',
                    text: 'Quick Query'
                },
                {
                    name: 'logout',
                    style: 'float: right;',
                    icon: 'ios-exit-outline',
                    text: 'Logout'
                },
                {
                    name: 'userInfo',
                    style: 'float: right;',
                    icon: 'md-person',
                    text: JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).realname
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
        },
        updateActiveMenuName (to) {
            this.activeMenuName = to.name
        }
    },
    watch: {
        $route: {
            handler: 'updateActiveMenuName',
            immediate: true
        }
    }
});
