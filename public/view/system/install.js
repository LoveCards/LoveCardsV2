const { createApp } = Vue;
const BaseEntity = new Base();

createApp({
    data() {
        return {
            inst: {
                activeIndex: 0,
                nowTabText: '🤗HI~LoveCards',
            },
            request: {
                Environment: {},
                DbConfig: {},
                InstallLock: {},
                CreateRsa: {}
            }
        }
    },
    mounted() {
        //MDUI组
        this.mdui = {};
        this.mdui.inst = new mdui.Tab('#tab');
        this.mdui.$ = mdui.$;

        this.getCheckEnvironment();
    },
    computed: {

    },
    methods: {
        instNext() {
            this.mdui.inst.next();
            this.inst.activeIndex = this.mdui.inst.activeIndex;
            // if (checkNext()) {
            //     this.inst.nowTabText = this.mdui.$(this.mdui.inst.$tabs[this.inst.activeIndex]).text();
            // }
        },
        instPrev() {
            this.mdui.inst.prev();
            this.inst.activeIndex = this.mdui.inst.activeIndex;
            this.inst.nowTabText = this.mdui.$(this.mdui.inst.$tabs[this.inst.activeIndex]).text();
        },
        // checkNext() {

        //     return {
        //         status: true,
        //         function: () => {

        //         }
        //     };
        // },
        getCheckEnvironment() {
            console.log(BaseEntity.RequestApiUrl('get', 'SystemInstallCheckEnvironment', { inti: () => { } }));;
        },
    }
}).mount('#app');