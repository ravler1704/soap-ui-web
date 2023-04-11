import './styles/app.css';
import './bootstrap';
import { createApp } from 'vue'
import _ from 'lodash';

import VueJsonPretty from 'vue-json-pretty';
import 'vue-json-pretty/lib/styles.css';

require('./css/app.scss');


createApp(window.vueapp = {
    data() {
        return {
            test: 'its working',
            count: 0,
            trans: '',
            soapFunctions: '',
            soapResponse: '',
            sendRequestRoute: null,
            wsdl: '',
            login: '',
            password: '',
            isLoading: false
        }
    },
    components: {
        VueJsonPretty
    },
    mounted() {
        debugger;
        this.trans = this.$el.parentElement.getAttribute('data-trans');
        this.soapFunctions = JSON.parse(this.$el.parentElement.getAttribute('data-soap-functions'));
        this.soapResponse = JSON.parse(this.$el.parentElement.getAttribute('data-soap-response'));
        this.sendRequestRoute = JSON.parse(this.$el.parentElement.getAttribute('data-send-request-route'));
        this.wsdl = JSON.parse(this.$el.parentElement.getAttribute('data-wsdl'));
        this.login = JSON.parse(this.$el.parentElement.getAttribute('data-login'));
        this.password = JSON.parse(this.$el.parentElement.getAttribute('data-password'));
    },
    methods: {
        getRequestMethodHref: function (methodName) {
            // this.soapFunctions;
            let queryParams = [];
            _.forEach(this.soapFunctions, (el) => {
                _.forEach(el.paramList, (param) => {
                    if (el.methodName === methodName) {
                        queryParams.push({
                            'name': param.name,
                            'value': param.value,
                        });
                    }
                });
            });
            let otherParamsStr = '';
            _.forEach(queryParams, (param) => {
                otherParamsStr += '&' + param.name + '=' + param.value;
            });
            return '?methodName=' + methodName + otherParamsStr;
        },
        sendRequest: function(soapFunction) {
            debugger;
            self = this;
            this.isLoading = true;
            $.ajax({
                url: "/send_request",
                type: 'post',
                dataType: 'json',
                data: {
                    'soapFunction': soapFunction,
                    'loginData': {
                        'wsdl': self.wsdl,
                        'login': self.login,
                        'password': self.password,
                    }
                },
                error: function (data, textStatus, errorThrown) {
                    debugger;
                    if (data.responseJSON.detail) {
                        self.soapResponse = data.responseJSON.detail
                    }
                    alert("Ошибка при выполнении запроса");
                },
                success: function (data) {
                    debugger;
                    self.soapResponse = data.soapResponse;
                    alert("Успешно");
                }
            });
            // if (data && data.successMessage) {
            //     this.postList = this.postList.concat(data.data);
            //     this.isLoading = false;
            // } else {
            //     this.isLoading = false;
            //     alert('Ошибка');
            // }

        }
    },
    computed: {

    },
    delimiters: ['${', '}$']
}).mount('#app')