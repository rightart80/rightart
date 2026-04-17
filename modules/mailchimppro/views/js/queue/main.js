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
 *
 * @var axios
 * @var queueUrl
 */
const {createApp} = Vue

window.avgResponseTime = null;
axios.interceptors.request.use((config) => {
    window.app.currentlyRunningRequests++;
    config.metadata = {startTime: new Date()}
    return config;
});

axios.interceptors.response.use((config) => {
    config.config.metadata.endTime = new Date();
    config.duration = config.config.metadata.endTime - config.config.metadata.startTime;
    if (window.app.avgResponseTime === null) {
        window.app.avgResponseTime = config.duration;
    } else {
        window.app.avgResponseTime = (window.app.avgResponseTime + (config.duration)) / 2;
    }
    window.app.currentlyRunningRequests--;
    return config;
});

window.app = createApp({
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
        processJob() {
            axios
                .post(
                    window.queueUrl + '&action=runJob'
                )
                .catch((error) => {
                    if (error.message) {
                        this.showError(error.message);
                    } else if (error.response) {
                        this.showError(error.response.data)
                    } else if (error.request) {
                        this.showError(error.response.data)
                    }
                })
                .then(response => {
                    if (typeof response != 'undefined') {
                        if (typeof response.data != 'undefined' && typeof response.data.message != 'undefined' && typeof response.data.message.success != 'undefined') {
                            var requestSuccess = response.data.message.success;
							if (typeof response.data.message.jobType != 'undefined') {
								var jobType = response.data.message.jobType;
								switch(jobType) {
								  case 'product':
									this.remainingProductsToSync--;
                                    if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedProductId = response.data.message.data.id_entity;
                                    }
									break;
								  case 'customer':
									this.remainingCustomersToSync--;
                                    if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedCustomerId = response.data.message.data.id_entity;
                                    }
									break;
								  case 'cartRule':
                                    this.remainingCartRulesToSync--;
									if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedCartRuleId = response.data.message.data.id_entity;
                                    }
									break;
								  case 'order':
									this.remainingOrdersToSync--;
                                    if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedOrderId = response.data.message.data.id_entity;
                                    }
									break;
                                  case 'cart':
									this.remainingCartsToSync--;
                                    if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedCartId = response.data.message.data.id_entity;
                                    }
									break;  
								  case 'newsletterSubscriber':
                                    this.remainingNewsletterSubscribersToSync--;
									if (typeof response.data.message.data != 'undefined' && typeof response.data.message.data.id_entity != 'undefined') {
                                        this.lastSyncedNewsletterSubscriberId = response.data.message.data.id_entity;
                                    }
									break;
								}
							}
                        }
                        else {
                            var requestSuccess = false;
                        }
                        if (requestSuccess) {
                            this.jobsCompleted++;
                        }
                        else {
                            if (typeof response.data.message.message != 'undefined') {
                                this.showError(response.data.message.message);
                            }
                        }
                        this.numberOfJobsAvailable = response.data.numberOfJobsAvailable;
                        this.numberOfJobsInFlight = response.data.numberOfJobsInFlight;
                        /* this.remainingProductsToSync = response.data.perType.product ?? 0;
                        this.remainingCustomersToSync = response.data.perType.customer ?? 0;
                        this.remainingCartRulesToSync = response.data.perType.cartRule ?? 0;
                        this.remainingOrdersToSync = response.data.perType.order ?? 0;
                        this.remainingCartsToSync = response.data.perType.cart ?? 0;
                        this.remainingNewsletterSubscribersToSync = response.data.perType.newsletterSubscriber ?? 0; */
                        if (!this.numberOfJobsAvailable && !this.numberOfJobsInFlight) {
                            this.runWorker = false;
                            this.syncCompleted = true;
                        }
                        
                        /* this.lastSyncedProductId = response.data.lastSyncedProductId;
                        this.lastSyncedCustomerId = response.data.lastSyncedCustomerId;
                        this.lastSyncedCartRuleId = response.data.lastSyncedCartRuleId;
                        this.lastSyncedOrderId = response.data.lastSyncedOrderId;
                        this.lastSyncedCartId = response.data.lastSyncedCartId;
                        this.lastSyncedNewsletterSubscriberId = response.data.lastSyncedNewsletterSubscriberId; */
                    }
                    /* else {
                        this.getQueueStats();
                    } */
                });
        },
        clearJobs() {
            axios
                .post(
                    window.queueUrl + '&action=clearJobs'
                )
                .catch((error) => {
                    if (error.response) {
                        this.showError(error.response.data)
                    } else if (error.request) {
                        this.showError(error.response.data)
                    } else {
                        this.showError(error.message)
                    }
                })
                .then(response => {
                    this.numberOfJobsAvailable = response.data.numberOfJobsAvailable;
                    this.numberOfJobsInFlight = response.data.numberOfJobsInFlight;
					this.jobsCleared = true;
                    /* this.lastSyncedProductId = response.data.lastSyncedProductId;
                    this.lastSyncedCustomerId = response.data.lastSyncedCustomerId;
                    this.lastSyncedCartRuleId = response.data.lastSyncedCartRuleId;
                    this.lastSyncedOrderId = response.data.lastSyncedOrderId;
                    this.lastSyncedCartId = response.data.lastSyncedCartId;
                    this.lastSyncedNewsletterSubscriberId = response.data.lastSyncedNewsletterSubscriberId; */
                })
        },
        getJobProcessRequest() {
            return axios.post(
                window.queueUrl + '&action=runJob'
            );
        },
        showError(message) {
            Toastify({
                text: message,
                duration: 2000,
                close: true,
                gravity: "top",
                position: 'center',
                style: {
                    background: "#ff0000",
                },
                stopOnFocus: false,
            }).showToast();
        },
		/* getQueueStats() {
			axios
				.post(
					window.queueUrl + '&action=getQueueStats'
				)
				.catch((error) => {
					if (error.message) {
                        this.showError(error.message);
                    } else if (error.response) {
                        this.showError(error.response.data)
                    } else if (error.request) {
                        this.showError(error.response.data)
                    }
				})
				.then(response => {
					this.numberOfJobsAvailable = response.data.numberOfJobsAvailable;
					this.numberOfJobsInFlight = response.data.numberOfJobsInFlight;
					this.remainingProductsToSync = response.data.perType.product ?? 0;
                    this.remainingCustomersToSync = response.data.perType.customer ?? 0;
					this.remainingCartRulesToSync = response.data.perType.cartRule ?? 0;
                    this.remainingOrdersToSync = response.data.perType.order ?? 0;
                    this.remainingCartsToSync = response.data.perType.cart ?? 0;
					this.remainingNewsletterSubscribersToSync = response.data.perType.newsletterSubscriber ?? 0;
				})
		} */
    },
    watch: {
        // whenever question changes, this function will run
        runWorker(newValue) {
            if (newValue === false) {
                this.jobsCompleted = 0;
            }
        }
    },
    mounted() {
        window.setInterval(
            () => {
                if (this.runWorker && this.currentlyRunningRequests <= this.maxParallelRequests) {
                    this.processJob()
                }
            },
            400
        )
    },
    data() {
        return {
			syncCompleted: false,
			jobsCleared: false,
            currentlyRunningRequests: 0,
            runWorker: false,
            numberOfJobsAvailable: window.numberOfJobsAvailable ?? 0,
            originalNumberOfJobs: window.numberOfJobsAvailable ?? 0,
            numberOfJobsInFlight: window.numberOfJobsInFlight ?? 0,
            maxParallelRequests: 7,
            jobsCompleted: 0,
            avgResponseTime: null,
            initialProductsToSync: window.numberOfJobsAvailablePerType.product ?? 0,
            initialCustomersToSync: window.numberOfJobsAvailablePerType.customer ?? 0,
			initialCartRulesToSync: window.numberOfJobsAvailablePerType.cartRule ?? 0,
            initialOrdersToSync: window.numberOfJobsAvailablePerType.order ?? 0,
            initialCartsToSync: window.numberOfJobsAvailablePerType.cart ?? 0,
			initialNewsletterSubscribersToSync: window.numberOfJobsAvailablePerType.newsletterSubscriber ?? 0,
            initialMergeTagPromoCodeToSync: window.numberOfJobsAvailablePerType.mergeTagPromoCode ?? 0,
            remainingProductsToSync: window.numberOfJobsAvailablePerType.product ?? 0,
            remainingCustomersToSync: window.numberOfJobsAvailablePerType.customer ?? 0,
            remainingCartRulesToSync: window.numberOfJobsAvailablePerType.cartRule ?? 0,
            remainingOrdersToSync: window.numberOfJobsAvailablePerType.order ?? 0,
            remainingCartsToSync: window.numberOfJobsAvailablePerType.cart ?? 0,
            remainingNewsletterSubscribersToSync: window.numberOfJobsAvailablePerType.newsletterSubscriber ?? 0,
            remainingMergeTagPromoCodeToSync: window.numberOfJobsAvailablePerType.mergeTagPromoCode ?? 0,
            lastSyncedProductId: window.lastSyncedProductId,
			lastSyncedCustomerId: window.lastSyncedCustomerId,
            lastSyncedCartRuleId: window.lastSyncedCartRuleId,
			lastSyncedOrderId: window.lastSyncedOrderId,
            lastSyncedCartId: window.lastSyncedCartId,
			lastSyncedNewsletterSubscriberId: window.lastSyncedNewsletterSubscriberId,
        }
    }
}).mount('#app')