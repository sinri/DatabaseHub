Vue.component('top-banner', {
    template: `
        <i-menu mode="horizontal" theme="dark"
            :active-name="activeMenuName"
            @on-select="onMenuItemSelected">
            <template v-for="item in menuItems">
                <template v-if="item.children">
                    <submenu v-if="!item.admin || isAdmin"
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
                </template>
                
                <template v-else>
                   <menu-item v-if="!item.admin || isAdmin"
                        :key="item.name"
                        :name="item.name"
                        :style="item.style">
                        <Icon :type="item.icon" v-if="item.icon" /> {{ item.text }}
                    </menu-item>
                </template>
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
                    admin: true,
                    children: [
                        {
                            name: 'databaseListPage',
                            icon: 'ios-cube',
                            text: 'Databases'
                        },
                        {
                            name: 'permissionsPage',
                            icon: 'ios-people',
                            text: 'Permissions'
                        },
                        {
                            name: 'processesPage',
                            icon: 'ios-backspace',
                            text: 'Processes'
                        },
                        {
                            name: 'workersPage',
                            icon: 'ios-pulse',
                            text: 'Workers'
                        }
                    ]
                },
                {
                    name: 'quickQueryPage',
                    icon: 'md-paper',
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
    computed: {
        isAdmin () {
            return JSON.parse(SinriQF.cookies.getCookie('DatabaseHubUser')).userType === 'ADMIN'
        }
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
                case 'quickQueryPage':
                    const {href} = router.resolve({name});

                    window.open(href, '_blank');

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
