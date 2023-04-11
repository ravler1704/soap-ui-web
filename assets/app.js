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
        this.soapFunctions = JSON.parse(this.$el.parentElement.getAttribute('data-soap-functions'));
        this.soapResponse = JSON.parse(this.$el.parentElement.getAttribute('data-soap-response'));
        this.sendRequestRoute = JSON.parse(this.$el.parentElement.getAttribute('data-send-request-route'));
        this.wsdl = JSON.parse(this.$el.parentElement.getAttribute('data-wsdl'));
        this.login = JSON.parse(this.$el.parentElement.getAttribute('data-login'));
        this.password = JSON.parse(this.$el.parentElement.getAttribute('data-password'));
    },
    methods: {
        getRequestMethodHref: function (methodName) {
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
                    if (data.responseJSON.detail) {
                        self.soapResponse = data.responseJSON.detail
                    }
                    alert("Request execution error");
                },
                success: function (data) {
                    self.soapResponse = data.soapResponse;
                    alert("Successfully");
                }
            });
        }
    },
    computed: {

    },
    delimiters: ['${', '}$']
}).mount('#app')