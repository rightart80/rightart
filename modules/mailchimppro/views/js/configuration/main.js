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
 * @var mailchimp
 */

const {createApp} = Vue

window.app = createApp({
    methods: {
        saveSettings(reason, showResponseMessage, callbackOnSuccess, callbackOnError) {
            if (!this.preventSave) {
                this.isSaving = true;
                axios
                    .post(
                        window.configurationUrl + '&action=saveSettings',
                        {
                            action: 'saveSettings',
                            selectedPreset: this.selectedPreset,
                            multiInstanceMode: this.multiInstanceMode,
                            cronjobBasedSync: this.cronjobBasedSync,
                            syncProducts: this.syncProducts,
                            syncCustomers: this.syncCustomers,
                            syncCartRules: this.syncCartRules,
                            syncOrders: this.syncOrders,
                            syncCarts: this.syncCarts,
                            syncCartsPassw: this.syncCartsPassw,
                            syncNewsletterSubscribers: this.syncNewsletterSubscribers,
                            statusForPending: this.statusForPending,
                            statusForRefunded: this.statusForRefunded,
                            statusForCancelled: this.statusForCancelled,
                            statusForShipped: this.statusForShipped,
                            statusForPaid: this.statusForPaid,
                            productDescriptionField: this.productDescriptionField,
                            existingOrderSyncStrategy: this.existingOrderSyncStrategy,
                            productSyncFilterActive: this.productSyncFilterActive,
                            productSyncFilterVisibility: this.productSyncFilterVisibility,
                            customerSyncFilterEnabled: this.customerSyncFilterEnabled,
                            customerSyncFilterNewsletter: this.customerSyncFilterNewsletter,
                            customerSyncFilterPeriod: this.customerSyncFilterPeriod,
                            customerSyncTagDefaultGroup: this.customerSyncTagDefaultGroup,
                            customerSyncTagGender: this.customerSyncTagGender,
                            customerSyncTagLanguage: this.customerSyncTagLanguage,
                            cartRuleSyncFilterStatus: this.cartRuleSyncFilterStatus,
                            cartRuleSyncFilterExpiration: this.cartRuleSyncFilterExpiration,
                            newsletterSubscriberSyncFilterPeriod: this.newsletterSubscriberSyncFilterPeriod,
                            productImageSize: this.productImageSize,
                            listId: this.listId,
                            storeSynced: this.storeSynced,
                            logQueue: this.logQueue,
                            queueStep: this.queueStep,
                            queueAttempt: this.queueAttempt,
                            logCronjob: this.logCronjob,
                            showDashboardStats: this.showDashboardStats,
                            promoOverridesEnabled: this.promoOverridesEnabled,
                        }
                    )
                    .then((response) => {
                            this.isSaving = false;
                            this.getEntityCount();
                            if (showResponseMessage !== false) {
                                this.showSuccess('Update successful!');
                            }
                            if (reason == 'multiInstanceMode') {
                                setTimeout(function () {
                                    location.reload();
                                }, 1500);
                            }
                            if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                                callbackOnSuccess();
                            } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                                this[callbackOnSuccess](...callbackOnSuccessParams);
                            }
                        }
                    );
            }
        },
        markReadJsonJobs(reason, showResponseMessage) {
            this.isSaving = true;
            axios
                .post(
                    window.configurationUrl + '&action=markReadJsonJobs',
                    {
                        action: 'markReadJsonJobs',                        
                    }
                )
                .then((response) => {
                        this.isSaving = false;
                        this.getEntityCount();

                        if (showResponseMessage !== false) {
                            this.showSuccess('Update successful!');
                        }

                        setTimeout(function () {
                            location.reload();
                        }, 100);                        
                    }
                )
        },
        markReadAutoList(reason, showResponseMessage) {
            this.isSaving = true;
            axios
                .post(
                    window.configurationUrl + '&action=markReadAutoList',
                    {
                        action: 'markReadAutoList',                        
                    }
                )
                .then((response) => {
                        this.isSaving = false;
                        this.getEntityCount();

                        if (showResponseMessage !== false) {
                            this.showSuccess('Update successful!');
                            $(".alert-auto-audience-sync").hide();
                        }

                        // setTimeout(function () {
                        //     location.reload();
                        // }, 100);                        
                    }
                )
        },
        syncStoresScript() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=syncStoresScript', 
                    {
                        action: 'ajaxProcessSyncStoresScript'
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        if (response.data.hasError === false) {
                            this.showSuccess(response.data.successMessage);                            
                        }
                        else {
                            this.showError(response.data.errorMessage);
                        }
                    }
                )
        },
        syncStore() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=syncStores', 
                    {
                        action: 'ajaxProcessSyncStores'
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        if (response.data.hasError === false) {
                            this.showSuccess(response.data.successMessage);
                            this.storeSynced = true;
                            $("#content > .bootstrap .alert-info").addClass('hidden');
                        }
                        else {
                            this.showError(response.data.errorMessage);
                            this.storeSynced = false;
                        }
                    }
                )
        },
        getEntityCount() {
            axios
                .post(
                    window.configurationUrl + '&action=getEntityCount',
                    {
                        action: 'getEntityCount',
                    }
                )
                .then((response) => {
                        this.numberOfCartRulesToSync = response.data.cartRules;
                        this.numberOfCustomersToSync = response.data.customers;
                        this.numberOfProductsToSync = response.data.products;
                        this.numberOfOrdersToSync = response.data.orders;
                        this.numberOfNewsletterSubscribersToSync = response.data.newsletterSubscribers;
                    }
                )
        },
        updateStaticContent() {
            axios
                .post(
                    window.configurationUrl + '&action=updateStaticContent',
                    {
                        action: 'ajaxProcessUpdateStaticContent',
                    }
                )
                .then((response) => {
                        this.cronjobLogContent = response.data.cronjobLogContent;
                        this.lastSyncedProductId = response.data.lastSyncedProductId;
                        this.lastSyncedCustomerId = response.data.lastSyncedCustomerId;
                        this.lastSyncedCartRuleId = response.data.lastSyncedCartRuleId;
                        this.lastSyncedOrderId = response.data.lastSyncedOrderId;
                        this.lastSyncedCartId = response.data.lastSyncedCartId;
                        this.lastSyncedNewsletterSubscriberId = response.data.lastSyncedNewsletterSubscriberId;
                        this.lastCronjob = response.data.lastCronjob;
                        this.lastCronjobExecutionTime = response.data.lastCronjobExecutionTime;
                        this.totalJobs = response.data.totalJobs;
                    }
                )
        },
        disconnect() {
            if (confirm("Do you really want to log out?")) {
                axios
                    .post(
                        window.configurationUrl + '&action=disconnect',
                        {
                            action: 'disconnect',
                        }
                    )
                    .then((response) => {
                            this.accountInfo = response.data.accountInfo;
                            this.validApiKey = response.data.validApiKey;
                            
                            $('#content > .bootstrap .alert-info').addClass('hidden');
                        }
                    )
            }
        },
        oauthStart() {
            let width = 500;
            let height = 900;
            let left = (screen.width / 2) - (width / 2);
            let top = (screen.height / 2) - (height / 2);

            window.open(
                window.middlewareUrlUpgraded,
                "McAuthMiddleware",
                'width=' + width + ', height=' + height + ', top=' + top + ', left=' + left + ",resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes"
            );
        },
        setCurrentPage(currentPage) {
            this.currentPage = currentPage;
            const url = new URL(window.location);
            url.hash = '#' + currentPage;
            window.history.pushState({}, '', url);
            
            this.updateStaticContent();
        },
        getCurrentPage(defaultValue) {
            let validApiKey = window.mailchimp.validApiKey;
            let listId = window.mailchimp.listId;
            let storeSynced = window.mailchimp.storeSynced;
            let selectedPreset = window.mailchimp.selectedPreset;
            let lastCronjob = window.mailchimp.lastCronjob;
            if (!defaultValue) {
                validApiKey = this.validApiKey;
                listId = this.listId;
                storeSynced = this.storeSynced;
                selectedPreset = this.selectedPreset;
                lastCronjob = this.lastCronjob;
            }
            let currentPage = window.location.hash.slice(1);
            if (validApiKey && (!selectedPreset || (!currentPage && !(listId && storeSynced && selectedPreset && lastCronjob)))) {
                window.location.hash = 'presets';
                return 'presets';
            }
            if (!currentPage) {
                currentPage = 'presets';
            }
            return currentPage;
        },
        isPanelStepAvailable(step) {
            switch(step) {
                case 'sync-store':
                    return true;
                case 'select-preset':
                    return this.selectedPreset || (this.validApiKey && this.listId && this.storeSynced && !this.selectedPreset);
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.cronjobBasedSync;
                default:
                    return false;
            }
        },
        isPanelStepActive(step) {
            switch(step) {
                case 'sync-store':
                    return !(this.validApiKey && this.listId && this.storeSynced);
                case 'select-preset':
                    return (this.validApiKey && this.listId && this.storeSynced && (!this.selectedPreset || (this.selectedPreset && !this.cronjobBasedSync))) || (this.validApiKey && this.listId && this.storeSynced && this.lastCronjob);
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.cronjobBasedSync && !this.lastCronjob;
                default:
                    return false;
            }
        },
        isPanelStepDone(step) {
            switch(step) {
                case 'sync-store':
                    return this.validApiKey && this.listId && this.storeSynced;
                case 'select-preset':
                    return this.validApiKey && this.selectedPreset;
                case 'setup-cronjob':
                    return this.validApiKey && this.listId && this.storeSynced && this.selectedPreset && this.lastCronjob;
                default:
                    return false;
            }
        },
        selectPanelStep(event) {
            if (event.target.classList.contains('panel-step') || event.target.closest('.panel-step')) {
                let currentStep = event.target.classList.contains('panel-step') ? event.target : event.target.closest('.panel-step');
                let currentStepContent = currentStep.dataset.stepTarget;
                // if (!currentStep) {
                //     currentStep = event.target.closest('.panel-step').dataset.stepTarget;
                // }
                if ($(currentStep).length && $('.' + currentStepContent).length) {
                    $(currentStep).addClass('active').siblings('.panel-step').removeClass('active');
                    $('.' + currentStepContent).addClass('active').siblings('.step-content').removeClass('active');
                }
            }
        },
        selectPreset(event) {
            if (event.target.closest('.panel') && event.target.closest('.panel').dataset.preset) {
                let currentPreset = event.target.closest('.panel').dataset.preset;
                let selectedPresetDefaults = this.definedPresets[currentPreset]['config-values'];

                // check if all keys in the selectedPresetDefaults object are present in the current data (this) object object:
                if (selectedPresetDefaults && Object.keys(selectedPresetDefaults).every(key => key in this)) {
                    // prevent running save function for every changed option
                    this.preventSave = true;
                    // update current data values:
                    Object.keys(selectedPresetDefaults).forEach(key => {
                        // if (key in this) {
                            this[key] = selectedPresetDefaults[key];
                        // }
                    });
                    // update selected preset value:
                    this.selectedPreset = currentPreset;
                    // wait for watchers to finish
                    setTimeout(function (_this) {
                        _this.preventSave = false;
                        if (_this.selectedPreset == 'custom') {
                            // when the selected preset is custom, only delete existing jobs, they would have to add them manually
                            _this.saveSettings('selectPreset', true, () => {_this.clearJobs('selectPreset', true)});
                        } else {
                            // else delete existing jobs, and add them based on the selected preset automatically
                            _this.saveSettings('selectPreset', true, () => {_this.clearJobs('selectPreset', true, () => {_this.pushSetupJobsToQueue('selectPreset', true)})});
                        }
                    }, 200, this);
                } else {
                    this.showError('Incorrect preset-defaults configuration!');
                }
            }
        },
        // set the current preset based on the current configuration data
        manageSelectedPreset() {
            let determinedPreset = this.determinePreset();
            if (this.selectedPreset && this.selectedPreset != determinedPreset) {
                // prevent running save function when changing the preset by configuration data
                this.preventSave = true;
                this.selectedPreset = determinedPreset;
                setTimeout(function (_this) {
                    _this.preventSave = false;
                    // update selected preset value:
                    _this.saveSettings('selectedPreset', false);
                }, 100, this);
            }
        },
        determinePreset() {
            const definedPresets = this.definedPresets;

            for (const key in definedPresets) {
                const configValues = definedPresets[key]['config-values'];
                const isMatch = Object.keys(configValues).every((configKey) => {
                    const dataValue = this[configKey];
                    const configValue = configValues[configKey];

                    if (Array.isArray(configValue)) {
                        // Check if both arrays are deeply equal
                        return Array.isArray(dataValue) && JSON.stringify(dataValue) === JSON.stringify(configValue);
                    }

                    // Check if primitive values match
                    return dataValue === configValue;
                });

                if (isMatch) {
                    // console.log(key);
                    if (key == 'free' && this.selectedPreset != 'free' && !this.isFreePresetAvailable()) {
                        return 'custom';
                    }
                    return key; // Return the matching main key
                }
            }
            // console.log('custom');

            return 'custom'; // Return 'custom' if no match is found
        },
        isFreePresetAvailable() {
            return this.accountInfo && this.accountInfo.total_subscribers && this.accountInfo.total_subscribers <= this.definedPresets['free']['list-member-limit'];
        },
        dropDownToggle(event) {
            let dropdownItem = event.target.closest('[aria-expanded]');
            if (dropdownItem) {
                dropdownItem.setAttribute("aria-expanded", !(dropdownItem.getAttribute("aria-expanded") == 'true'));
            }
        },
        getPromoData() {
            return {
                promoId: this.currentPromoId,
                promoName: $("#PROMO_NAME").val().trim(),
                promoPrefix: $("#PROMO_PREFIX").val().trim(),
                promoSuffix: $("#PROMO_SUFFIX").val().trim(),
                promoExpiration: $("#PROMO_EXPIRATION").val().trim(),
                // promoCampaign: $("#PROMO_CAMPAIN").val().trim(),
                promoReduction: this.validatePromoReduction("PROMO_REDUCTION"),
                promoStatus: +$("[name='PROMO_STATUS']:checked").val(),
                promoReductionType: +$("[name='PROMO_REDUCTION_TYPE']:checked").val(),
            };
        },
        async addNewPromo() {
            const promoData = this.getPromoData();

            const validationErrors = this.validatePromoData(promoData);
            if (validationErrors.length) {
                this.showError(validationErrors.join(' '));
                return;
            }

            await axios
                .post(
                    window.configurationUrl + '&action=addNewPromo',
                    {
                        action: 'addNewPromo',
                        data: promoData
                    }
                )
                .then(async ({ data }) => {
                    if (data.success) {
                        this.currentPromoId = data.promo.id;
                        localStorage.setItem('currentPromoId', this.currentPromoId);
                        this.showSuccess(data.message);
                        if (data.promo.status == 1) {
                            this.showGeneratePromoElements();
                            await this.generateCodes(this.currentPromoId);
                            if (!$('#promoTable tbody tr[data-id=' + this.currentPromoId + ']').length) {
                                window.location.reload();
                            }
                        }
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch((err) => {
                    this.showError('An error occurred while processing your request.' + err.response.data.detail);
                });
        },
        validatePromoReduction(promoReductionId) {
            let promoReductionValue = $("#" + promoReductionId).val().trim();
            let fixedValue = (isNaN(parseFloat(promoReductionValue)) ? '' : Math.abs(parseFloat(promoReductionValue)));
            if (promoReductionValue !== fixedValue) {
                $("#" + promoReductionId).val(fixedValue);
            }
            return fixedValue;
        },
        validatePromoData(data) {
            const errors = [];
            let target = false;
            if (data.target && data.target.id) {
                target = '#' + data.target.id;
            }
            if (!data || target) {
                data = this.getPromoData();
            }
            if (data) {
                if (!data.promoName && (!target || (target && target == '#PROMO_NAME'))) {
                    errors.push('Please enter a name for the new promo code!');
                    $("#PROMO_NAME").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoName) {
                        $("#PROMO_NAME").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!data.promoPrefix && (!target || (target && target == '#PROMO_PREFIX'))) {
                    errors.push('Please enter a prefix for the new promo code!');
                    $("#PROMO_PREFIX").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoPrefix) {
                        $("#PROMO_PREFIX").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!data.promoExpiration && (!target || (target && target == '#PROMO_EXPIRATION'))) {
                    errors.push('Please enter an expiration date for the new promo code!');
                    $("#PROMO_EXPIRATION").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoExpiration) {
                        $("#PROMO_EXPIRATION").closest('.form-group').removeClass('has-error');
                    }
                }
                if (!(data.promoReduction && isFinite(data.promoReduction)) && (!target || (target && target == '#PROMO_REDUCTION'))) {
                    errors.push('Please enter a valid reduction value!');
                    $("#PROMO_REDUCTION").closest('.form-group').removeClass('has-success').addClass('has-error');
                } else {
                    if (data.promoReduction && isFinite(data.promoReduction)) {
                        $("#PROMO_REDUCTION").closest('.form-group').removeClass('has-error');
                    }
                }
                // $("#PROMO_SUFFIX").closest('.form-group').addClass('has-success').removeClass('has-error');
                // $("[name='PROMO_REDUCTION_TYPE']").closest('.form-group').addClass('has-success').removeClass('has-error');
                // $("[name='PROMO_STATUS']").closest('.form-group').addClass('has-success').removeClass('has-error');
            }
            return errors;
        },
        editPromo(promoId) {
            // Save the promoId to localStorage
            localStorage.setItem('currentPromoId', promoId);

            axios
                .post(window.configurationUrl + '&action=getPromoDetails', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        const promo = data.data;
                        const count = data.count;
                        const progress = Math.round(((count.totalAll - count.remain) / count.totalAll) * 100);
                        this.populatePromoForm(promo);
                        $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress).html(progress + '%');
                        $('#totalCount').html(count.total);
                        this.setCurrentPage('addNewPromo');

                        if (promo.status == 1) {
                            this.showGeneratePromoElements();
                        }
                        if (progress > 0 && progress < 100) {
                            $('.col-generate-codes').show();
                        }
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo stats.');
                });
        },
        viewPromo(promoId) {
            axios
                .post(window.configurationUrl + '&action=getPromoCodes', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        this.promoCodes = data.codes;
                        this.setCurrentPage('promocodes');
                        const table = $('#promocodesTable');
                        if ($.fn.dataTable.isDataTable(table)) {
                            table.DataTable().destroy(); // Destroy the existing DataTable
                        }

                        setTimeout(function () {
                            // Reinitialize the DataTable after a short delay for previous DataTable destroy
                            table.DataTable({
                                "paging": true,
                                "searching": true,
                                "ordering": true,
                                "order": [[0, 'desc']],
                                "columnDefs": [
                                    { "orderable": false, "targets": [0, 5] }
                                ]
                            });
                        }, 500);
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo codes.');
                });
        },
        deletePromo(promoId) {
            if (confirm("Do you really want to delete? This will also delete the Merge Field from Mailchimp.")) {
            axios
                .post(window.configurationUrl + '&action=deletePromo', { id: promoId })
                .then(({ data }) => {
                    if (data.success) {
                        this.showSuccess(data.message);
                        this.setCurrentPage('promo');
                    } else {
                        this.showError(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo.');
                });
            }
        },
        deletePromocode(promocode) {
            if (confirm("Do you really want to delete?")) {
            axios
                .post(window.configurationUrl + '&action=deletePromocode', { id: promocode })
                .then(({ data }) => {
                    if (data.success) {
                        this.showSuccess(data.message);
                        this.setCurrentPage('promo');
                    } else {
                        this.showError(data.message);
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                })
                .catch(() => {
                    this.showError('An error occurred while fetching promo.');
                });
            }
        },
        deleteSelectedPromocodes() {
            if (
                this.selectedPromoCodes.length > 0 &&
                confirm("Do you really want to delete the selected promocodes?")
            ) {
                axios
                    .post(window.configurationUrl + '&action=deleteBulkPromocodes', { ids: this.selectedPromoCodes })
                    .then(({ data }) => {
                        if (data.success) {
                            this.showSuccess(data.message);
                            this.setCurrentPage('promo');
                        } else {
                            this.showError(data.message);
                        }
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    })
                    .catch(() => {
                        this.showError('An error occurred while deleting promocodes.');
                    });
            }
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedPromoCodes = this.promoCodes.map((code) => code.id);
            } else {
                this.selectedPromoCodes = [];
            }
        },
        updatePromo() {
            const promoData = this.getPromoData();

            const validationErrors = this.validatePromoData(promoData);
            if (validationErrors.length) {
                this.showError(validationErrors.join(' '));
                return;
            }

            axios
                .post(window.configurationUrl + '&action=updatePromo', { data: promoData })
                .then(async ({ data }) => {
                    if (data.success) {
                        this.currentPromoId = data.promo.id;
                        localStorage.setItem('currentPromoId', this.currentPromoId);
                        this.showSuccess(data.message);
                        // this.setCurrentPage('promo');
                        // window.location.reload();
                        if (promoData.promoStatus == 1) {
                            this.showGeneratePromoElements();
                            await this.generateCodes(this.currentPromoId);
                        }
                        //if (!$('#promoTable tbody tr[data-id=' + this.currentPromoId + ']').length) {
                            window.location.reload();
                        //}
                    } else {
                        this.showError(data.message);
                    }
                })
                .catch(() => {
                    this.showError('An error occurred while updating promo.');
                });
        },
        populatePromoForm(promo) {
            if (promo === 'new') {
                localStorage.removeItem('currentPromoId');
                this.hideGeneratePromoElements();
            }
            if (!promo || promo === 'new') {
                promo = {
                    id: 0,
                    name: '',
                    prefix: '',
                    suffix: '',
                    end_date: '',
                    campaign_id: '',
                    reduction: '',
                    status: 0,
                    reduction_type: 0
                }
                $('#progressBar').css('width', '0%').attr('aria-valuenow', '0').html('0%');
                $('#totalCount').html(0);
            }
            $("#PROMO_NAME").val(promo.name);
            $("#PROMO_PREFIX").val(promo.prefix);
            $("#PROMO_SUFFIX").val(promo.suffix);
            $("#PROMO_EXPIRATION").val(promo.end_date);
            // $("#PROMO_CAMPAIN").val(promo.campaign_id);
            $("#PROMO_REDUCTION").val(promo.reduction);
            $(`[name='PROMO_STATUS'][value="${promo.status}"]`).prop('checked', true);
            $(`[name='PROMO_REDUCTION_TYPE'][value="${promo.reduction_type}"]`).prop('checked', true);
            this.currentPromoId = promo.id;
            if (promo.status == 0) {
                this.hideGeneratePromoElements();
            }
        },
        showGeneratePromoElements() {
            $('.generate-promo-codes-hr, .generate-promo-codes').show();
        },
        hideGeneratePromoElements() {
            $('.generate-promo-codes-hr, .generate-promo-codes').hide();
        },
        async generateCodes(promoId) {
            let offset = 0;
            const progressBar = document.getElementById('progressBar');
            const totalCountDisplay = document.getElementById('totalCount');

            if (!promoId) {
                await this.addNewPromo();
                promoId = this.currentPromoId;
            }

            const updateProgressBar = (progress) => {
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                progressBar.innerText = `${progress}%`;
            };

            let totalEmails = 0;

            while (true) {
                try {
                    const response = await axios.post(window.configurationUrl + '&action=generateCodes', {
                        id: promoId,
                        offset: offset,
                    });

                    const { success, message, generated_count, remaining, total, next_offset } = response.data;

                    if (!success) {
                        this.showError(`Error: ${message}`);
                        break;
                    }

                    if (!totalEmails) {
                        totalEmails = total;
                    }

                    offset = next_offset;

                    // Update progress bar
                    const progress = Math.min(100, Math.round(((totalEmails - remaining) / totalEmails) * 100));
                    updateProgressBar(progress);

                    if (remaining <= 0) {
                        this.showSuccess(message);
                        updateProgressBar(100);
                        break;
                    }
                    if (!totalEmails) {
                        totalCountDisplay.innerText = totalEmails;
                    }

                    this.showSuccess(`${message}. ${generated_count} codes generated in this batch. Remaining: ${remaining}`);
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('A critical error occurred during code generation.');
                    break;
                }
            }
        },
        syncPromo(promoId) {

            // if(!promoId) {
            //     await this.addNewPromo();
            //     promoId = this.currentPromoId;
            // }

            axios
                .post(
                    window.configurationUrl + '&action=syncPromo', 
                    {
                        action: 'ajaxProcessSyncPromo',
                        promoId: promoId,
                    }
                )
                .then((response) => {
                        // this.showLoader = false;
                        if (response.data.success === true) {
                            this.showSuccess(response.data.message);
                            // this.storeSynced = true;
                            // $("#content > .bootstrap .alert-info").addClass('hidden');
                        }
                        else {
                            this.showError(response.data.message);
                            // this.storeSynced = false;
                        }
                    }
                )
        },
        arrayEquals(a, b) {
            if (a.length !== b.length) return false;
            const uniqueValues = new Set([...a, ...b]);
            for (const v of uniqueValues) {
                const aCount = a.filter(e => e === v).length;
                const bCount = b.filter(e => e === v).length;
                if (aCount !== bCount) return false;
            }
            return true;
        },
        getUniqueConfigKeys(data) {
            const keys = new Set();
            // Iterate through each top-level key
            Object.values(data).forEach(section => {
                if (section['config-values']) {
                    // Add the keys from 'config-values' to the set
                    Object.keys(section['config-values']).forEach(key => keys.add(key));
                }
            });

            return Array.from(keys); // Convert the Set to an Array
        },
        pushSetupJobsToQueue(reason, showResponseMessage) {
			this.isSaving = true;
            let requests = [];
            // stores
            // requests.push(axios.post(window.configurationUrl + '&action=syncStores', {action: 'ajaxProcessSyncStores'}))
            let jobsAddedToQueue = 0;
            if (this.syncProducts) {
                requests.push(axios.post(window.configurationUrl + '&action=addProductsToQueue', {action: 'ajaxProcessAddProductsToQueue'}));
                
                // filling the specific price table with initial values
                requests.push(axios.post(window.configurationUrl + '&action=initializeSpecificPrices', {action: 'ajaxProcessSpecificPrices'}));
                jobsAddedToQueue += this.numberOfProductsToSync;
            }
            if (this.syncCustomers) {
                requests.push(axios.post(window.configurationUrl + '&action=addCustomersToQueue', {action: 'ajaxProcessAddCustomersToQueue'}));
                jobsAddedToQueue += this.numberOfCustomersToSync;
            }
            if (this.syncOrders) {
                requests.push(axios.post(window.configurationUrl + '&action=addOrdersToQueue', {action: 'ajaxProcessAddOrdersToQueue'}));
                jobsAddedToQueue += this.numberOfOrdersToSync;
            }
            if (this.syncCartRules) {
                requests.push(axios.post(window.configurationUrl + '&action=addCartRulesToQueue', {action: 'ajaxProcessAddCartRulesToQueue'}));
                jobsAddedToQueue += this.numberOfCartRulesToSync;
            }
            if (this.syncNewsletterSubscribers) {
                requests.push(axios.post(window.configurationUrl + '&action=addNewsletterSubscribersToQueue', {action: 'ajaxProcessAddNewsletterSubscribersToQueue'}));
                jobsAddedToQueue += this.numberOfNewsletterSubscribersToSync;
            }

            axios
                .all(requests)
                .then(
                    axios.spread((...responses) => {
                        console.log(responses);
						this.jobsAddedToQueue = true;
						this.isSaving = false;
                        if (this.syncProducts) this.queuedTypes.products = true;
                        if (this.syncCustomers) this.queuedTypes.customers = true;
                        if (this.syncCartRules) this.queuedTypes.cartRules = true;
                        if (this.syncOrders) this.queuedTypes.orders = true;
                        if (this.syncNewsletterSubscribers) this.queuedTypes.newsletterSubscribers = true;
                        if (showResponseMessage) {
                            if (jobsAddedToQueue) {
                                this.showSuccess('Jobs successfully added to queue!');
                                this.totalJobs = jobsAddedToQueue;
                            } else {
                                console.log('No jobs have been added to queue!');
                            }
                        }
                    })
                )
                .catch(errors => {
                    // react on errors.
                    console.error(errors);
					this.isSaving = false;
                    if (showResponseMessage) {
                        this.showSuccess('Something went wrong adding jobs to queue!');
                    }
                });
        },
        pushSingleTypeToQueue(type) {
            const typeConfig = {
                products: {
                    loadingKey: 'queueLoadingProducts',
                    countKey: 'numberOfProductsToSync',
                    actions: [
                        { action: 'addProductsToQueue', ajaxAction: 'ajaxProcessAddProductsToQueue' },
                        { action: 'initializeSpecificPrices', ajaxAction: 'ajaxProcessSpecificPrices' }
                    ],
                    label: 'Product'
                },
                customers: {
                    loadingKey: 'queueLoadingCustomers',
                    countKey: 'numberOfCustomersToSync',
                    actions: [
                        { action: 'addCustomersToQueue', ajaxAction: 'ajaxProcessAddCustomersToQueue' }
                    ],
                    label: 'Customer'
                },
                cartRules: {
                    loadingKey: 'queueLoadingCartRules',
                    countKey: 'numberOfCartRulesToSync',
                    actions: [
                        { action: 'addCartRulesToQueue', ajaxAction: 'ajaxProcessAddCartRulesToQueue' }
                    ],
                    label: 'Cart rule'
                },
                orders: {
                    loadingKey: 'queueLoadingOrders',
                    countKey: 'numberOfOrdersToSync',
                    actions: [
                        { action: 'addOrdersToQueue', ajaxAction: 'ajaxProcessAddOrdersToQueue' }
                    ],
                    label: 'Order'
                },
                newsletterSubscribers: {
                    loadingKey: 'queueLoadingNewsletterSubscribers',
                    countKey: 'numberOfNewsletterSubscribersToSync',
                    actions: [
                        { action: 'addNewsletterSubscribersToQueue', ajaxAction: 'ajaxProcessAddNewsletterSubscribersToQueue' }
                    ],
                    label: 'Newsletter subscriber'
                },
            };

            const config = typeConfig[type];
            if (!config) return;

            this[config.loadingKey] = true;

            let requests = config.actions.map(a =>
                axios.post(window.configurationUrl + '&action=' + a.action, { action: a.ajaxAction })
            );

            axios.all(requests)
                .then(axios.spread((...responses) => {
                    this[config.loadingKey] = false;
                    this.queuedTypes[type] = true;
                    this.jobsAddedToQueue = true;
                    this.totalJobs = (this.totalJobs || 0) + this[config.countKey];
                    this.showSuccess(config.label + ' jobs added to queue!');
                }))
                .catch(errors => {
                    console.error(errors);
                    this[config.loadingKey] = false;
                    this.showError('Failed to add ' + config.label.toLowerCase() + ' jobs to queue.');
                });
        },
        clearJobs(reason, showResponseMessage, callbackOnSuccess, callbackOnError) {
            if (this.totalJobs && this.totalJobs > 0) {
                this.isSaving = true;
                axios
                    .post(
                        window.queueUrl + '&action=clearJobs'
                    )
                    .catch((error) => {
                        this.isSaving = false;
                        if (error.response) {
                            this.showError(error.response.data)
                        } else if (error.request) {
                            this.showError(error.response.data)
                        } else {
                            this.showError(error.message)
                        }
                        if (showResponseMessage) {
                            this.showSuccess('Something went wrong clearing previous jobs!');
                        }
                    })
                    .then(response => {
                        this.isSaving = false;
                        if (showResponseMessage) {
                            if (reason == 'selectPreset') {
                                this.showSuccess('Previous jobs deleted successfully!');
                            } else {
                                this.showSuccess('Jobs deleted successfully!');
                            }
                        }
                        if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                            callbackOnSuccess();
                        } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                            this[callbackOnSuccess](...callbackOnSuccessParams);
                        }
                        this.totalJobs = (response && response.data && response.data.numberOfJobsAvailable) ? response.data.numberOfJobsAvailable : 0;
                    })
            } else {
                console.log('Total jobs: ' + this.totalJobs + ' - no deletion needed.');
                // if (showResponseMessage) {
                //     this.showSuccess('No previous jobs have been deleted!');
                // }
                if (callbackOnSuccess && typeof callbackOnSuccess == 'function') {
                    callbackOnSuccess();
                } else if (callbackOnSuccess && typeof this[callbackOnSuccess] == 'function') {
                    this[callbackOnSuccess](...callbackOnSuccessParams);
                }
            }
        },
        deleteMailchimpEcommerceData() {
            if (confirm("Do you really want to delete?")) {
                this.showLoader = true;
                axios
                    .post(
                        window.configurationUrl + '&action=deleteEcommerceData',
                        {
                            action: 'ajaxProcessDeleteEcommerceData',
                        }
                    )
                    .then((response) => {
                            this.showLoader = false;
                            if (response.data.hasError === false) {
                                this.showSuccess(response.data.successMessage);
                            }
                            else {
                                this.showError(response.data.errorMessage);
                            }
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        }
                    )
            }
        },
        executeCronjob(e) {
            e.preventDefault();
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=executeCronjob&secure=' + window.cronjobSecureToken,
                    {
                        action: 'ajaxProcessExecuteCronjob',
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        if (response.data.errors) {
                            this.showError(response.data.errors);
                        }
                        else {
                            if (response.data.result) {
                                this.showSuccess(response.data.result);
                            }
                        }

                        this.updateStaticContent();
                    }
                )
        },
        clearCronjobLog() {
            if (confirm("Do you really want to clear the cronjob log?")) {
                this.showLoader = true;
                axios
                    .post(
                        window.configurationUrl + '&action=clearCronjobLog',
                        {
                            action: 'ajaxProcessClearCronjobLog',
                        }
                    )
                    .then((response) => {
                            this.showLoader = false;
                            if (response.data.hasError === false) {
                                this.showSuccess(response.data.successMessage);
                            }
                            else {
                                this.showError(response.data.errorMessage);
                            }

                            this.updateStaticContent();
                        }
                    )
            }
        },
        refreshReports() {
            this.showLoader = true;
            axios
                .post(
                    window.configurationUrl + '&action=refreshReports',
                    {
                        action: 'refreshReports',
                    }
                )
                .then((response) => {
                        this.showLoader = false;
                        $('.panel-statistics #mailchimp-reports').replaceWith(response.data.statistics);
                    }
                )
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
		showSuccess(message) {
            Toastify({
                text: message,
                duration: 2000,
                close: true,
                gravity: "top",
                position: 'center',
                style: {
                    background: "#1a8f35",
                },
                stopOnFocus: false,
            }).showToast();
        },
        viewPromoStats(idPromo) {
            if (idPromo) {
                localStorage.setItem('currentPromoStatId', idPromo);
            }
            this.currentPromoId = idPromo;

            axios
                .post(window.statsUrl + '&action=getStats', { idPromo: idPromo })
                .then(({ data }) => {
                    if (data.success) {
                        console.log(data);

                        $('#statsStart').datepicker("option", 'minDate', data.campaign.start_date);
                        $('#statsStart').datepicker("option", 'maxDate', data.campaign.end_date);

                        $('#statsEnd').datepicker("option", 'minDate', data.campaign.start_date);
                        $('#statsEnd').datepicker("option", 'maxDate', data.campaign.end_date);

                        this.setCurrentPage('promoStats');

                        this.statsName = data.campaign.name;

                        this.statAllCodes = data.stats.all_codes;
                        this.statUsedCodes = data.stats.used_codes;
                        //this.statUnUsedCodes = data.stats.unused_codes;
                        this.statConversion = data.stats.conversion;
                        this.statFrequency = data.stats.frequency;

                        var pieOptions = {
                            series: data.pieData.series,
                            chart: {
                                height: 315,
                                type: 'pie',
                            },
                            labels: data.pieData.labels,
                            fill: {
                                colors: data.pieData.colors
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        // width: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        };

                        var barOptions = {
                            series: [{
                                name: data.barData.seriesLabel,
                                data: data.barData.series ?? [0],
                            }],
                            chart: {
                                height: 350,
                                type: 'bar',
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 0,
                                    dataLabels: {
                                        position: 'top', // top, center, bottom
                                    },
                                }
                            },
                            colors: ['#241c15'],
                            dataLabels: {
                                enabled: true,
                                // formatter: function (val) {
                                //   return val + "%";
                                // },
                                offsetY: -20,
                                style: {
                                    fontSize: '12px',
                                    colors: ["#304758"]
                                }
                            },

                            xaxis: {
                                categories: data.barData.labels ?? [new Date().toLocaleDateString()],
                                position: 'bottom',
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                                crosshairs: {
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            colorFrom: '#D8E3F0',
                                            colorTo: '#BED1E6',
                                            stops: [0, 100],
                                            opacityFrom: 0.4,
                                            opacityTo: 0.5,
                                        }
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                }
                            },
                            yaxis: {
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false,
                                },
                                labels: {
                                    show: false,
                                    //   formatter: function (val) {
                                    //     return val + "%";
                                    //   }
                                }

                            },
                            title: {
                                text: data.barData.mainLabel,
                                position: 'top',
                                align: 'center',
                                margin: 10,      
                                offsetX: 0,      
                                offsetY: 0,    
                                floating: false,
                                style: {
                                  fontSize: '16px',
                                  fontWeight: 'bold',
                                  color: '#444'
                                }
                            }
                        };

                        var conversionOptions = {
                            series: [
                                {
                                    name: data.conversionData.seriesLabel,
                                    data: data.conversionData.seriesOrderCart,
                                },
                            ],
                            chart: {
                                height: 350,
                                type: 'line',
                                dropShadow: {
                                    enabled: true,
                                    color: '#000',
                                    top: 18,
                                    left: 7,
                                    blur: 10,
                                    opacity: 0.5
                                },
                                zoom: {
                                    enabled: false
                                },
                                toolbar: {
                                    show: false
                                }
                            },
                            colors: ['#ffe01b', '#545454'],
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return val + "%";
                                }
                            },
                            stroke: {
                                curve: 'smooth'
                            },
                            grid: {
                                borderColor: '#e7e7e7',
                                row: {
                                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                    opacity: 0.5
                                },
                            },
                            markers: {
                                size: 1
                            },
                            xaxis: {
                                categories: data.conversionData.labels,
                                position: 'bottom',
                            },
                            title: {
                                position: 'top',
                                text: data.conversionData.mainLabel,
                                align: 'center',
                                margin: 10,      
                                offsetX: 0,      
                                offsetY: 0,    
                                floating: false,
                                style: {
                                  fontSize: '16px',
                                  fontWeight: 'bold',
                                  color: '#444'
                                }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'right',
                                floating: true,
                                offsetY: -25,
                                offsetX: -5
                            },
                            tooltip: {
                                y: {
                                  formatter: function(value) {
                                    return value + "%";
                                  }
                                }
                              }
                        };


                        if (typeof this.pieChart != 'undefined') {
                            this.pieChart.destroy();
                        }
                        this.pieChart = new ApexCharts(document.querySelector("#pieChart"), pieOptions);

                        if (typeof this.barChart != 'undefined') {
                            this.barChart.destroy();
                        }
                        this.barChart = new ApexCharts(document.querySelector("#barChart"), barOptions);
                        if (data.barData.series && data.barData.labels) {
                            document.querySelector("#barChart").style.display = 'block';
                        } else {
                            document.querySelector("#barChart").style.display = 'none';
                        }
                        
                        if (typeof this.conversionChart != 'undefined') {
                            this.conversionChart.destroy();
                        }
                        this.conversionChart = new ApexCharts(document.querySelector("#conversionChart"), conversionOptions);
                        

                        setTimeout(() => {
                            this.pieChart.render();
                            this.barChart.render();
                            this.conversionChart.render();
                        }, 300);

                    } else {
                        this.showError(data.message);
                    }
                })
                .catch((e) => {
                    console.log(e);
                    this.showError('An error occurred while fetching promo details.');
                });
        },
        getStats()
        {
            var request = {
                idPromo: this.currentPromoId,
                dateStart: $('#statsStart').val(),
                dateEnd: $('#statsEnd').val(),
            }

            axios
            .post(window.statsUrl + '&action=getStatsByDate', request)
            .then(({ data }) => {
                if (data.success == false) {
                    this.showError(data.message);
                    return;
                }

                console.log(data);
                this.barChart.updateOptions({
                    series: [{
                        data: data.barData.series,
                    }],
                    xaxis: {
                        categories: data.barData.labels
                    }
                });
                if (data.barData.series && data.barData.labels) {
                    document.querySelector("#barChart").style.display = 'block';
                } else {
                    document.querySelector("#barChart").style.display = 'none';
                }

                this.conversionChart.updateOptions({
                    series: [{
                        data: data.conversionData.seriesOrderCart,
                    }],
                    xaxis: {
                        categories: data.conversionData.labels
                    }
                });
                

            });
        },
        
        /**
         * Toggle promo overrides and handle installation if needed
         * 
         * @param {boolean} enabled - Whether overrides should be enabled
         */
        togglePromoOverrides(enabled) {
            this.showLoader = true;
            
            // If disabling and auto-installed, use the remove method
            if (!enabled && this.promoOverridesAutoInstalled) {
                axios
                    .post(
                        window.configurationUrl + '&action=removePromoOverrides',
                        {
                            action: 'ajaxProcessRemovePromoOverrides'
                        }
                    )
                    .then((response) => {
                        this.showLoader = false;
                        
                        if (response.data.success) {
                            this.showSuccess(response.data.message);
                            this.promoOverridesAutoInstalled = false;
                        } else {
                            this.showError(response.data.message || 'An error occurred');
                            // Reset the toggle if there was an error
                            this.promoOverridesEnabled = true;
                        }
                    })
                    .catch((error) => {
                        this.showLoader = false;
                        this.showError('Error during override removal: ' + (error.response?.data?.message || error.message));
                        // Reset the toggle if there was an error
                        this.promoOverridesEnabled = true;
                        console.error('Error removing promo overrides:', error);
                    });
            } else {
                // Existing code for enabling/disabling
                axios
                    .post(
                        window.configurationUrl + '&action=togglePromoOverrides',
                        {
                            action: 'ajaxProcessTogglePromoOverrides',
                            enabled: enabled
                        }
                    )
                    .then((response) => {
                        this.showLoader = false;
                        
                        if (response.data.success) {
                            switch (response.data.overrideStatus) {
                                case 'conflicts':
                                    this.showError(response.data.message);
                                    this.overrideConflicts = response.data.conflicts || [];
                                    
                                    // Create a more detailed conflict message
                                    let conflictDetails = '';
                                    this.overrideConflicts.forEach(conflict => {
                                        conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                                        if (conflict.methods && conflict.methods.length > 0) {
                                            conflictDetails += 'Conflicting methods:<br><ul>';
                                            conflict.methods.forEach(method => {
                                                conflictDetails += `<li>${method}</li>`;
                                            });
                                            conflictDetails += '</ul>';
                                        }
                                    });
                                    
                                    // Store the detailed conflict message
                                    this.conflictDetailsHtml = conflictDetails;
                                    this.showManualInstallationDialog = true;
                                    
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'installed':
                                    this.showSuccess(response.data.message);
                                    this.promoOverridesAutoInstalled = true;
                                    break;
                                    
                                case 'auto_installed':
                                    this.showSuccess(response.data.message);
                                    this.promoOverridesAutoInstalled = true;
                                    break;
                                    
                                case 'failed':
                                    this.showError(response.data.message);
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'error':
                                    this.showError(response.data.message);
                                    // Reset the toggle if installation failed
                                    this.promoOverridesEnabled = false;
                                    break;
                                    
                                case 'disabled':
                                    this.showSuccess(response.data.message);
                                    break;
                                    
                                default:
                                    // Unknown status
                                    console.warn('Unknown override status:', response.data.overrideStatus);
                            }
                        } else {
                            this.showError(response.data.message || 'An error occurred');
                            
                            if (response.data.requiresManualInstallation) {
                                this.overrideConflicts = response.data.conflicts || [];
                                
                                // Create a more detailed conflict message
                                let conflictDetails = '';
                                this.overrideConflicts.forEach(conflict => {
                                    conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                                    if (conflict.methods && conflict.methods.length > 0) {
                                        conflictDetails += 'Conflicting methods:<br><ul>';
                                        conflict.methods.forEach(method => {
                                            conflictDetails += `<li>${method}</li>`;
                                        });
                                        conflictDetails += '</ul>';
                                    }
                                });
                                
                                // Store the detailed conflict message
                                this.conflictDetailsHtml = conflictDetails;
                                this.showManualInstallationDialog = true;
                            }
                            
                            // Reset the toggle if there was an error
                            this.promoOverridesEnabled = false;
                        }
                    })
                    .catch((error) => {
                        this.showLoader = false;
                        this.showError('Error during override toggle: ' + (error.response?.data?.message || error.message));
                        // Reset the toggle if there was an error
                        this.promoOverridesEnabled = false;
                        console.error('Error toggling promo overrides:', error);
                    });
            }
        },
        
        /**
         * Mark overrides as manually installed
         */
        markOverridesManuallyInstalled() {
            this.showLoader = true;
            this.preventToggleOverrides = true; // Set flag to prevent watcher from triggering
            
            axios
                .post(
                    window.configurationUrl + '&action=markOverridesInstalled',
                    {
                        action: 'ajaxProcessMarkOverridesInstalled'
                    }
                )
                .then((response) => {
                    this.showLoader = false;
                    
                    if (response.data.success) {
                        this.showSuccess(response.data.message);
                        this.promoOverridesEnabled = true;
                        this.showManualInstallationDialog = false;
                        this.manuallyInstalledOverrides = true; // Set flag for manually installed overrides
                    } else {
                        this.showError(response.data.message || 'An error occurred');
                    }
                    
                    // Reset the flag after a short delay to ensure the watcher doesn't trigger
                    setTimeout(() => {
                        this.preventToggleOverrides = false;
                    }, 100);
                })
                .catch((error) => {
                    this.showLoader = false;
                    this.preventToggleOverrides = false; // Reset flag in case of error
                    this.showError('Error: ' + (error.response?.data?.message || error.message));
                    console.error('Error marking overrides as installed:', error);
                });
        },
        
        /**
         * Check the current status of overrides
         */
        checkOverrideStatus() {
            // Use the promoOverridesConflicts variable directly instead of making an AJAX call
            if (!this.manuallyInstalledOverrides && this.promoOverridesConflicts && this.promoOverridesConflicts.length > 0) {
                this.overrideConflicts = this.promoOverridesConflicts;
                
                // Create a more detailed conflict message
                let conflictDetails = '';
                this.overrideConflicts.forEach(conflict => {
                    conflictDetails += `<strong>File: ${conflict.file}</strong><br>`;
                    if (conflict.methods && conflict.methods.length > 0) {
                        conflictDetails += 'Conflicting methods:<br><ul>';
                        conflict.methods.forEach(method => {
                            conflictDetails += `<li>${method}</li>`;
                        });
                        conflictDetails += '</ul>';
                    }
                });
                
                // Store the detailed conflict message
                this.conflictDetailsHtml = conflictDetails;
                this.showManualInstallationDialog = true;
                
                // Set preventToggleOverrides flag to prevent AJAX call
                this.preventToggleOverrides = true;
                
                // Reset the toggle if installation failed
                this.promoOverridesEnabled = false;
                
                // Reset the flag after a short delay to ensure the watcher doesn't trigger
                setTimeout(() => {
                    this.preventToggleOverrides = false;
                }, 100);
            }
        }
    },
    watch: {
        listId: function () {
            if (this.listId) {
                this.storeSynced = false;
            }
            this.saveSettings();
        },
        multiInstanceMode: function () {
            if (this.storeInstanceMode != this.multiInstanceMode) {
                this.storeSynced = false;
                this.listId = false;
                this.saveSettings("multiInstanceMode", false);
            }
            else {
                this.saveSettings("multiInstanceMode", true);
            }
        },
        selectedPreset: function (newValue, oldValue) {
            // if (oldValue || newValue == 'custom') {
            //     this.saveSettings();
            // } else {
            //     // automatically add jobs to queue on first preset selection
            //     // this.saveSettings('saveSettings', true, 'pushSetupJobsToQueue');
            //     this.saveSettings('saveSettings', true, () => {this.pushSetupJobsToQueue()});
            // }
            this.saveSettings();
        },
        cronjobBasedSync: function () {
            this.saveSettings();
        },
        syncProducts: function () {
            this.saveSettings();
        },
        syncCustomers: function () {
            this.saveSettings();
        },
        syncCartRules: function () {
            this.saveSettings();
        },
        syncOrders: function () {
            this.saveSettings();
        },
        syncCarts: function () {
            this.saveSettings();
        },
        syncCartsPassw: function () {
            this.saveSettings();
        },
        syncNewsletterSubscribers: function () {
            this.saveSettings();
        },
        statusForPending: function () {
            this.saveSettings();
        },
        statusForRefunded: function () {
            this.saveSettings();
        },
        statusForCancelled: function () {
            this.saveSettings();
        },
        statusForShipped: function () {
            this.saveSettings();
        },
        statusForPaid: function () {
            this.saveSettings();
        },
        productDescriptionField: function () {
            this.saveSettings();
        },
        existingOrderSyncStrategy: function () {
            this.saveSettings();
        },
        productSyncFilterActive: function () {
            this.saveSettings();
        },
        productSyncFilterVisibility: function () {
            this.saveSettings();
        },
        customerSyncFilterEnabled: function () {
            this.saveSettings();
        },
        customerSyncFilterNewsletter: function () {
            this.saveSettings();
        },
        customerSyncFilterPeriod: function () {
            this.saveSettings();
        },
        customerSyncTagDefaultGroup: function () {
            this.saveSettings();
        },
        customerSyncTagGender: function () {
            this.saveSettings();
        },
        customerSyncTagLanguage: function () {
            this.saveSettings();
        },
        cartRuleSyncFilterStatus: function () {
            this.saveSettings();
        },
        newsletterSubscriberSyncFilterPeriod: function () {
            this.saveSettings();
        },
        cartRuleSyncFilterExpiration: function () {
            this.saveSettings();
        },
        productImageSize: function () {
            this.saveSettings();
        },
		logQueue: function () {
            this.saveSettings();
        },
        showDashboardStats: function () {
            this.saveSettings();
        },
        promoOverridesEnabled: function (newValue) {
            // Only call togglePromoOverrides if preventToggleOverrides is false
            if (!this.preventToggleOverrides) {
                this.togglePromoOverrides(newValue);
            }
        },
		queueStepRaw: function () {
			if (!isNaN(parseInt(this.queueStepRaw)) && parseInt(this.queueStepRaw) > 0) {
				this.queueStepRaw = parseInt(this.queueStepRaw);
				if (this.queueStep != this.queueStepRaw) {
					this.queueStep = this.queueStepRaw;
					this.saveSettings();
				}
			}
			else {
				this.showError('Invalid queue step!');
			}
        },
		queueAttemptRaw: function () {
			if (!isNaN(parseInt(this.queueAttemptRaw)) && parseInt(this.queueAttemptRaw) > 0) {
				this.queueAttemptRaw = parseInt(this.queueAttemptRaw);
				if (this.queueAttempt != this.queueAttemptRaw) {
					this.queueAttempt = this.queueAttemptRaw;
					this.saveSettings();
				}
			}
			else {
				this.showError('Invalid queue max-trying time!');
			}
        },
        logCronjob: function () {
            this.saveSettings();
        }
    },
    mounted() {
        this.timer = setInterval(() => {
            if (this.getCurrentPage() !== this.currentPage) {
                this.currentPage = this.getCurrentPage();
            }
        }, 100);
        const savedPromoId = localStorage.getItem('currentPromoId');

        if (savedPromoId && window.location.hash === '#addNewPromo') {
            this.editPromo(savedPromoId); // Call editPromo with the saved ID
        }
        const currentPromoStatId = localStorage.getItem('currentPromoStatId');
        if (currentPromoStatId && window.location.hash === '#promoStats') {
            this.viewPromoStats(currentPromoStatId); // Call editPromo with the saved ID
        }

        window.addEventListener(
            "message",
            (event) => {
                if (event.origin !== window.middlewareUrl) {
                    return false;
                }
                if (event.data.hasOwnProperty('token') && event.data.hasOwnProperty('user')) {
                    const token = event.data.token + "-" + event.data.user.dc;
                    axios
                        .post(
                            window.configurationUrl + '&action=connect',
                            {
                                action: 'connect',
                                token: token
                            }
                        )
                        .then((response) => {
                                /* this.accountInfo = response.data.accountInfo;
                                this.validApiKey = response.data.validApiKey; */
                                
                                //setTimeout(function () {
                                    location.reload();
                                //}, 500);
                            }
                        )
                }
            },
            true
        );


        // Add dynamic watch to all the data which affect the current preset:
        // Get unique preset affecting config keys
        const uniquePresetConfigKeys = this.getUniqueConfigKeys(this.definedPresets);
        // Add watchers dynamically for each unique key
        uniquePresetConfigKeys.forEach(key => {
            if (this[key] !== undefined) {
                this.$watch(
                    key,
                    (newValue, oldValue) => {
                        // console.log(`Key '${key}' changed:`, oldValue, '->', newValue);
                        // manage selected preset only when the value of the watched key is changed manually and not by triggered from selectPreset()
                        if (!this.preventSave) {
        this.manageSelectedPreset();
        
        // Check override status on page load
        this.checkOverrideStatus();
                        }
                    },
                    { immediate: false } // Optional: Run the watcher immediately on initialization
                );
            }
        });

        this.checkOverrideStatus();
        this.manageSelectedPreset();
    },
        data() {
            return {
            isSaving: false,
            preventSave: false,
            showLoader: false,
			jobsAddedToQueue: false,
            queueLoadingProducts: false,
            queueLoadingCustomers: false,
            queueLoadingCartRules: false,
            queueLoadingOrders: false,
            queueLoadingNewsletterSubscribers: false,
            queuedTypes: {
                products: false,
                customers: false,
                cartRules: false,
                orders: false,
                newsletterSubscribers: false,
            },
            definedPresets: window.mailchimp.definedPresets,
            selectedPreset: window.mailchimp.selectedPreset,
            currentPage: this.getCurrentPage(true),
            currentPromoId: 0,
            promoCodes: [],
            selectedPromoCodes: [],
            selectAll: false,
            preventToggleOverrides: false,
            manuallyInstalledOverrides: window.mailchimp.manuallyInstalledOverrides,
            listId: window.mailchimp.listId,
            lists: window.mailchimp.lists,
            storeAlreadySynced: window.storeAlreadySynced ?? false,
            storeSynced: window.mailchimp.storeSynced,
            orderStates: window.mailchimp.orderStates,
            storeInstanceMode: window.mailchimp.multiInstanceMode,
            multiInstanceMode: window.mailchimp.multiInstanceMode,
            cronjobBasedSync: window.mailchimp.cronjobBasedSync,
            syncProducts: window.mailchimp.syncProducts,
            syncCustomers: window.mailchimp.syncCustomers,
            syncCartRules: window.mailchimp.syncCartRules,
            syncOrders: window.mailchimp.syncOrders,
            syncCarts: window.mailchimp.syncCarts,
            syncCartsPassw: window.mailchimp.syncCartsPassw,
            syncNewsletterSubscribers: window.mailchimp.syncNewsletterSubscribers,
            statusForPending: (window.mailchimp.statusForPending),
            statusForRefunded: (window.mailchimp.statusForRefunded),
            statusForCancelled: (window.mailchimp.statusForCancelled),
            statusForShipped: (window.mailchimp.statusForShipped),
            statusForPaid: (window.mailchimp.statusForPaid),
            productDescriptionField: window.mailchimp.productDescriptionField,
            existingOrderSyncStrategy: window.mailchimp.existingOrderSyncStrategy,
            productSyncFilterActive: (window.mailchimp.productSyncFilterActive),
            productSyncFilterVisibility: (window.mailchimp.productSyncFilterVisibility),
            customerSyncFilterEnabled: (window.mailchimp.customerSyncFilterEnabled),
            customerSyncFilterNewsletter: (window.mailchimp.customerSyncFilterNewsletter),
            customerSyncFilterPeriod: window.mailchimp.customerSyncFilterPeriod,
            customerSyncTagDefaultGroup: window.mailchimp.customerSyncTagDefaultGroup,
            customerSyncTagGender: window.mailchimp.customerSyncTagGender,
            customerSyncTagLanguage: window.mailchimp.customerSyncTagLanguage,
            cartRuleSyncFilterStatus: (window.mailchimp.cartRuleSyncFilterStatus),
            cartRuleSyncFilterExpiration: (window.mailchimp.cartRuleSyncFilterExpiration),
            newsletterSubscriberSyncFilterPeriod: window.mailchimp.newsletterSubscriberSyncFilterPeriod,
            productImageSize: window.mailchimp.productImageSize,
            token: window.mailchimp.token,
            validApiKey: window.mailchimp.validApiKey,
            accountInfo: window.mailchimp.accountInfo,
            numberOfCartRulesToSync: window.mailchimp.numberOfCartRulesToSync,
            numberOfCustomersToSync: window.mailchimp.numberOfCustomersToSync,
            numberOfProductsToSync: window.mailchimp.numberOfProductsToSync,
            numberOfOrdersToSync: window.mailchimp.numberOfOrdersToSync,
            numberOfNewsletterSubscribersToSync: window.mailchimp.numberOfNewsletterSubscribersToSync,
            logQueue: window.mailchimp.logQueue,
            queueStep: window.mailchimp.queueStep,
            queueStepRaw: window.mailchimp.queueStep,
            queueAttempt: window.mailchimp.queueAttempt,
            queueAttemptRaw: window.mailchimp.queueAttempt,
			logCronjob: window.mailchimp.logCronjob,
			cronjobLogContent: window.mailchimp.cronjobLogContent,
			lastSyncedProductId: window.mailchimp.lastSyncedProductId,
			lastSyncedCustomerId: window.mailchimp.lastSyncedCustomerId,
            lastSyncedCartRuleId: window.mailchimp.lastSyncedCartRuleId,
			lastSyncedOrderId: window.mailchimp.lastSyncedOrderId,
            lastSyncedCartId: window.mailchimp.lastSyncedCartId,
			lastSyncedNewsletterSubscriberId: window.mailchimp.lastSyncedNewsletterSubscriberId,
			lastCronjob: window.mailchimp.lastCronjob,
			lastCronjobExecutionTime: window.mailchimp.lastCronjobExecutionTime,
			totalJobs: window.mailchimp.totalJobs,
            showDashboardStats: window.mailchimp.showDashboardStats,
            statAllCodes: 0,
            statUsedCodes: 0,
            statConversion: 0,
            statFrequency: 0,
            promoOverridesEnabled: window.mailchimp.promoOverridesEnabled,
            promoOverridesAutoInstalled: window.mailchimp.promoOverridesAutoInstalled || false,
            promoOverridesConflicts : window.mailchimp.promoOverridesConflicts || false,
            overrideConflicts: [],
            showManualInstallationDialog: false,
        }
    }
});
window.app.component('multiselect', window.VueformMultiselect)
window.app.mount('#app')
