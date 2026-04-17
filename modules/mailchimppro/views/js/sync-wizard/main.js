/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */
import {createApp} from 'vue';
import {ConcurrencyManager} from './concurrency-manager.js';
import {} from './loading-effects.js';

window.avgResponseTime = null;
axios.interceptors.request.use((config) => {
    config.metadata = {startTime: new Date()}
    if (!config.headers.post.hasOwnProperty('SkipLoadingScreen')) {
        window.app.beforeHttpRequest();
    }
    return config;
});

axios.interceptors.response.use((config) => {
    window.app.afterHttpRequest();
    config.config.metadata.endTime = new Date();
    config.duration = config.config.metadata.endTime - config.config.metadata.startTime;
    if (window.app.avgResponseTime === null) {
        window.app.avgResponseTime = config.duration;
    } else {
        window.app.avgResponseTime = (window.app.avgResponseTime + (config.duration)) / 2;
    }
    return config;
});


window.app = createApp({
    data() {
        return {
            middleWareUrl: window.middlewareUrl,
            orderStates: window.orderStates,
            token: window.token,
            userName: window.userName,
            healthCheck: false,
            currentStep: 1,
            availableLists: [],
            selectedList: null,
            pendingStates: [],
            refundedStates: [],
            cancelledStates: [],
            shippedStates: [],
            paidStates: [],
            productIds: window.productIds,
            productsSynced: 0,
            customerIds: window.customerIds,
            customersSynced: 0,
            orderIds: window.orderIds,
            ordersSynced: 0,
            promoCodeIds: window.promoCodeIds,
            promoCodesSynced: 0,
            maxConcurrentRequests: 5,
            avgResponseTime: null
        }
    },
    methods: {
        padTo2Digits(num) {
            return num.toString().padStart(2, '0');
        },
        convertMsToMinutesSeconds(milliseconds) {
            const minutes = Math.floor(milliseconds / 60000);
            const seconds = Math.round((milliseconds % 60000) / 1000);

            return seconds === 60
                ? `${minutes + 1}:00`
                : `${minutes}:${this.padTo2Digits(seconds)}`;
        },
        oauthStart() {
            let windowObjectReference;
            const strWindowFeatures = "height=500,width=500,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes";
            windowObjectReference = window.open(
                this.middleWareUrl,
                "McAuthMiddleware",
                strWindowFeatures
            );
        },
        beforeHttpRequest() {
            this.$refs.appContainer.setAttribute('disabled', '');
            this.$refs.appContainer.style.filter = 'blur(4px)';
        },
        afterHttpRequest() {
            this.$refs.appContainer.removeAttribute('disabled', '');
            this.$refs.appContainer.style.filter = 'blur(0)';
        },
        syncStores() {
            axios
                .post(
                    window.wizardUrl + '&action=syncStores',
                    {
                        action: 'ajaxProcessSyncStores',
                    },
                    {
                        headers: {'SkipLoadingScreen': 'SkipLoadingScreen'}
                    }
                )
                .then((response) => {
                    this.currentStep = 5;
                })
                .catch(function (error) {
                    Toastify({
                        text: error.response.data.error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: 'center',
                        style: {
                            background: "#ff0000",
                        },
                        stopOnFocus: false,
                    }).showToast();
                });
        },
        proceedToStepFour() {
            axios
                .post(
                    window.wizardUrl + '&action=stateMapping',
                    {
                        action: 'ajaxProcessStateMapping',
                        states: {
                            'module-mailchimpproconfig-statuses-for-shipped': this.shippedStates,
                            'module-mailchimpproconfig-statuses-for-pending': this.pendingStates,
                            'module-mailchimpproconfig-statuses-for-cancelled': this.cancelledStates,
                            'module-mailchimpproconfig-statuses-for-paid': this.paidStates,
                            'module-mailchimpproconfig-statuses-for-refunded': this.refundedStates,
                        }
                    })
                .then((response) => {
                    this.currentStep = 5;
                    this.syncStores();
                    this.syncProducts();
                })
                .catch(function (error) {
                    Toastify({
                        text: error.response.data.error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: 'center',
                        style: {
                            background: "#ff0000",
                        },
                        stopOnFocus: false,
                    }).showToast();
                });
        },
        syncStates() {
            axios
                .post(
                    window.wizardUrl + '&action=getStates',
                    {
                        action: 'getStates',
                    }
                )
                .then((response) => {
                        this.pendingStates = JSON.parse(response.data.mapping['module-mailchimpproconfig-statuses-for-pending']);
                        this.refundedStates = JSON.parse(response.data.mapping['module-mailchimpproconfig-statuses-for-refunded']);
                        this.cancelledStates = JSON.parse(response.data.mapping['module-mailchimpproconfig-statuses-for-cancelled']);
                        this.shippedStates = JSON.parse(response.data.mapping['module-mailchimpproconfig-statuses-for-shipped']);
                        this.paidStates = JSON.parse(response.data.mapping['module-mailchimpproconfig-statuses-for-paid']);
                    }
                )
        },
        syncProducts() {
            axios
                .post(
                    window.wizardUrl + '&action=addProductsToQueue',
                    {
                        action: 'ajaxProcessAddProductsToQueue'
                    })
                .then(responses => {
                    this.currentStep = 6;
                    this.syncCustomers();
                });
        },
        syncCustomers() {
            axios
                .post(
                    window.wizardUrl + '&action=addCustomersToQueue',
                    {
                        action: 'ajaxProcessAddCustomersToQueue'
                    })
                .then(responses => {
                    this.currentStep = 7;
                    this.syncOrders();
                });
        },
        syncOrders() {
            axios
                .post(
                    window.wizardUrl + '&action=addOrdersToQueue',
                    {
                        action: 'ajaxProcessAddOrdersToQueue'
                    }
                    )
                .then(responses => {
                    this.currentStep = 8;
                    this.syncPromoCodes();
                });
        },
        syncPromoCodes() {
            axios
                .post(
                    window.wizardUrl + '&action=addPromoCodesToQueue',
                    {
                        action: 'ajaxProcessAddPromoCodesToQueue'
                    })
                .then(responses => {
                    this.currentStep = -1;
                });
        },
        saveAudienceConfig() {
            axios
                .post(
                    window.wizardUrl + '&action=listSelect',
                    {
                        action: 'listSelect',
                        listId: this.selectedList
                    })
                .then(() => {
                    this.currentStep = 3;
                    this.syncStates();


                })
                .catch(function (error) {
                    let message = "Unknown error";
                    if (error.response.data.error) {
                        message = error.response.data.error;
                    }
                    if (error.message) {
                        message = error.message;
                    }

                    Toastify({
                        text: message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: 'center',
                        style: {
                            background: "#ff0000",
                        },
                        stopOnFocus: false,
                    }).showToast();
                });
        },
        proceedToStepThree() {
            this.saveAudienceConfig();
        },
        proceedToStepTwo() {
            axios.post(
                window.wizardUrl + '&action=apiKey',
                {
                    action: 'apiKey',
                    apiKey: this.token
                })
                .then(() => {
                    this.currentStep = 2;
                    axios.post(
                        window.wizardUrl + '&action=getLists',
                        {
                            action: 'getLists',
                        })
                        .then((response) => {
                            this.availableLists = response.data.lists;
                            this.selectedList = response.data.selectedList;
                        })
                        .catch(function (error) {
                            Toastify({
                                text: error.response.data.error,
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: 'center',
                                style: {
                                    background: "#ff0000",
                                },
                                stopOnFocus: false,
                            }).showToast();
                        });
                })
                .catch(function (error) {
                    let message = "Unknown error";
                    if (error.response.data.error) {
                        message = error.response.data.error;
                    }
                    if (error.message) {
                        message = error.message;
                    }
                    Toastify({
                        text: message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: 'center',
                        style: {
                            background: "#ff0000",
                        },
                        stopOnFocus: false,
                    }).showToast();
                });
        }
    },
    watch: {
        // whenever question changes, this function will run
        currentStep(newStep, oldStep) {
            if (newStep === -1) {
                setTimeout(() => {
                    window.location.replace(window.workerUrl);
                }, 3000)
            }
        }
    },

    mounted() {
        window.addEventListener(
            "message",
            (event) => {
                if (event.origin !== this.middleWareUrl) {
                    return false;
                }
                if (event.data.hasOwnProperty('token') && event.data.hasOwnProperty('user')) {
                    this.token = event.data.token + "-" + event.data.user.dc;
                    this.userName = event.data.user.login.login_name;
                }
            },
            true
        );
    }
}).mount('#app')