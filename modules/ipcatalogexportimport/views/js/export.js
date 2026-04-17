/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

function triggerChanges() {
//        $('input[name="as"]').change();
//        $('.catalogEI_date').change();
//        $('.date_collapser').change();
    $('.catalogEI').find('form :input').change();
    var x = document.querySelectorAll('input[type=range]');
    for (var i = 0; i < x.length; i++) {
        x[i].dispatchEvent(new Event('input'));
    }

}
    
$(document).ready(function () {

//    var config = decodeURIComponent($('#product_export_form').serialize()).replace(/\+/g, ' ');
//    var cols = {};
//    $('#product_fields ul > li').each(function (index, element) {
//        cols[$(element).data('value')] = [$(element).text(), $(element).hasClass('selected')];
//    });
//    config += '&selectedColumns=' + JSON.stringify(cols);
//    console.log(config);

    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        showErrorMessage(message);
    };

    function showLoading() {
        $('#fader, #spinner').css('display', 'block');
    }

    function hideLoading() {
        $('#fader, #spinner').css('display', 'none');
    }

    function loadTemplates(templates) {
        var html = '', html2 = '';
        $.each(templates, function (index, value) {
            if (value.name === 'catalog_default') {
                html += '<li data-id="' + value.id_ipcatalogexport
                        + '" data-config=\'' + value.configuration
                        + '\' class="list-group-item"><b>' + default_template + '</b><span class="pull-right apply_span default_config">'
                    + '<button type="button" class="btn btn-default apply_config"><i class="icon-check"></i> ' + apply + '</button> | '
                    + '<a class="btn btn-default" href="' + controller_link + '&action=getSetting&st_id=' + value.id_ipcatalogexport + '"><i class="icon-download"></i></a></span></li>';
                html2 += '<option selected value="' + value.name + '">' + default_template + '</option>';
            } else {
                html += '<li data-id="' + value.id_ipcatalogexport
                        + '" data-config=\'' + value.configuration
                        + '\' class="list-group-item"><b>' + value.name + '</b><span class="pull-right apply_span">'
                    + '<button class="btn btn-default apply_config"><i class="icon-check"></i> ' + apply + '</button> | '
                    + '<a class="btn btn-default" href="' + controller_link + '&action=getSetting&st_id=' + value.id_ipcatalogexport + '"><i class="icon-download"></i></a> | '
                    + '<button type="button" class="btn btn-default delete_file"><i title="' + value.title
                    + '" class="icon-trash" style="color:#c50000" aria-hidden="true"></i></button></span></li>';
                html2 += '<option value="' + value.name + '">' + value.name + '</option>';
            }
        });
        $('#configs').html(html);
        $('#schedule_email_template').html(html2);
        $('#schedule_ftp_template').html(html2);
    }
    
    $('#product_export_form :input[name="categories[]"]').attr("disabled", "disabled");
    $('#product_export_form :input[name="category_whether_filter"]').change(function () {
        if (this.value == '0') {
            $('#product_export_form :input[name="categories[]"]').attr("disabled", "disabled");
        } else {
            $('#product_export_form :input[name="categories[]"]').removeAttr("disabled");
        }
    })
    
    $('#combination_export_form :input[name="categories[]"]').attr("disabled", "disabled");
    $('#combination_export_form :input[name="category_whether_filter"]').change(function () {
        if (this.value == '0') {
            $('#combination_export_form :input[name="categories[]"]').attr("disabled", "disabled");
        } else {
            $('#combination_export_form :input[name="categories[]"]').removeAttr("disabled");
        }
    })

    function updateSchedule(key, value) {
        showLoading();
        var scheduleData = {
            ajax_action: "updateSchedule",
            type: 'dml',
            key: key,
            value: value
        };
        $.post(controller_link, scheduleData, function (result) {
            hideLoading();
            var json = JSON.parse(result);
            if (json.type === 'success') {
                showSuccessMessage(json.message);
            } else {
                showErrorMessage(json.message);
            }
        });
    }

    // Submitting products
    $('#product_export_form').submit(function (e) {
        e.preventDefault();

        // Get selected/unselected ids from the data tables
        collectProducts();
//        collectAttributes();
        collectFeatures();
        collectManufacturers();
        collectSuppliers();
        collectCarriers();

        startExport('product');
    });

    // Submitting categories
    $('#category_export_form').submit(function (e) {
        e.preventDefault();

        startExport('category');
    });

    // Submitting warehouses
    $('#warehouse_export_form').submit(function (e) {
        e.preventDefault();

        collectWarehouses();

        startExport('warehouse');
    });

    // Submitting aliases
    $('#alias_export_form').submit(function (e) {
        e.preventDefault();

        collectAliases();

        startExport('alias');
    });

    // Submitting stores
    $('#store_export_form').submit(function (e) {
        e.preventDefault();

        collectStores();

        startExport('store');
    });

    // Submitting packs
    $('#pack_export_form').submit(function (e) {
        e.preventDefault();

        collectPacks();
        startExport('pack');
    });

    // Submitting combinations
    $('#combination_export_form').submit(function (e) {
        e.preventDefault();

        // Get selected/unselected ids from the data tables
        collectCombinations();
        collectAttributes();

        startExport('combination');
    });

    // Submitting discounts
    $('#discount_export_form').submit(function (e) {
        e.preventDefault();

        // Get selected/unselected ids from the data tables
        collectDiscounts();

        startExport('discount');
    });

    // Submitting brands
    $('#brand_export_form').submit(function (e) {
        e.preventDefault();

        collectBrands();

        startExport('brand');
    });

    // Submitting suppliers
    $('#supplier_export_form').submit(function (e) {
        e.preventDefault();

        collectSuppliers2();

        startExport('supplier');
    });

    // Submitting carriers
    $('#carrier_export_form').submit(function (e) {
        e.preventDefault();

        collectCarriers2();

        startExport('carrier');
    });

    // Submitting customers
    $('#customer_export_form').submit(function (e) {
        e.preventDefault();

        collectCustomers();
        collectGroups();

        startExport('customer');
    });

    // Submitting addresses
    $('#address_export_form').submit(function (e) {
        e.preventDefault();

        collectAddresses();

        startExport('address');

    });
    
    // Submitting groups
    $('#group_export_form').submit(function (e) {
        e.preventDefault();

        collectGroups2();

        startExport('group');
    });
    
    // Submitting features
    $('#feature_export_form').submit(function (e) {
        e.preventDefault();

        collectFeaturesForFeatures();
        collectFeatureValues();

        startExport('feature');
    });
    
    // Submitting attributes
    $('#attribute_export_form').submit(function (e) {
        e.preventDefault();

        collectAttributeGroups();
        collectAttributeValues();

        startExport('attribute');
    });

    var exportCancelRequest = false;
    var exportContinueRequest = false;
    var entity, limit;
    var speeds = [50, 100, 200, 500, 1000, 2000, 5000, 10000];

    function startExport(ent) {
        entity = ent;
        limit = speeds[parseInt($('#' + entity + '_speed').val())];
        exportNow(0, limit, -1, {}, 0);
        $('#exportProgress').modal('show');
    }

    function exportNow(offset, limit, total, crossStepsVariables, moreStep, fileId) {
        if (offset === 0) {
            updateProgressionInit();
            updateProgression(0, total, limit, false, moreStep, null);
        }

        var data = $('#' + entity + '_export_form').serializeArray();
        data.push({'name': 'crossStepsVars', 'value': JSON.stringify(crossStepsVariables)});

        // Get the selected fields (columns)
        var selected_columns = {};
        $('#' + entity + '_export_form').find('.scrollable ul li.selected').each(function (index, elem) {
            selected_columns[$(elem).data('value')] = $(elem).text();
        });
        data.push({'name': 'selectedColumns', 'value': JSON.stringify(selected_columns)});

        data.push({'name': 'fileId', 'value': fileId});

//        var startingTime = new Date().getTime();
        $.ajax({
            type: 'POST',
            url: controller_link + '&action=export&offset=' + offset + '&limit=' + limit + (moreStep > 0 ? '&moreStep=' + moreStep : ''),
            cache: false,
            dataType: "json",
            data: data,
            success: function (jsonData)
            {
                if (jsonData.totalCount) {
                    total = jsonData.totalCount;
                }

                if (jsonData.informations && jsonData.informations.length > 0) {
                    updateProgressionInfo('<li>' + jsonData.informations.join('</li><li>') + '</li>');
                }
                if (jsonData.warnings && jsonData.warnings.length > 0) {
                    updateProgressionError('<li>' + jsonData.warnings.join('</li><li>') + '</li>', true);
                }
                if (jsonData.errors && jsonData.errors.length > 0) {
                    updateProgressionError('<li>' + jsonData.errors.join('</li><li>') + '</li>', false);
                    return; // If errors, stops process
                }

                // Here, no errors returned
                if (!jsonData.isFinished == true) {
//                    // compute time taken by previous call to adapt amount of elements by call.
//                    var previousDelay = new Date().getTime() - startingTime;
//                    var targetDelay = 5000; // try to keep around 5 seconds by call
//                    // acceleration will be limited to 4 to avoid newLimit to increase too fast (NEVER reach 30 seconds by call!).
//                    var acceleration = Math.min(4, (targetDelay / previousDelay));
//                    // keep between 5 to 100 elements to process in one call
//                    var newLimit = Math.min(100, Math.max(5, Math.floor(limit * acceleration)));
                    var newOffset = offset + limit;
                    // update progression

                    updateProgression(jsonData.doneCount, total, jsonData.doneCount + limit, false, moreStep, jsonData.moreStepLabel);

                    if (exportCancelRequest == true) {
                        $('#exportProgress').modal('hide');
                        exportCancelRequest = false;
//                        window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
                        return; // stops execution
                    }

                    // process next group of elements
                    exportNow(newOffset, limit, total, jsonData.crossStepsVariables, moreStep, jsonData.fileId);

                    // checks if we could go over post_max_size setting. Warns when reach 90% of the actual setting
                    if (jsonData.nextPostSize >= jsonData.postSizeLimit * 0.9) {
                        var progressionDone = jsonData.doneCount * 100 / total;
                        var increase = Math.max(7, parseInt(jsonData.postSizeLimit / (progressionDone * 1024 * 1024))) + 1; // min 8MB
                        $('#export_details_post_limit_value').html(increase + " MB");
                        $('#export_details_post_limit').show();
                    }

                } else {
                    if (jsonData.oneMoreStep > moreStep) {
                        updateProgression(total, total, total, false, false, null); // do not close now
                        exportNow(0, limit, total, jsonData.crossStepsVariables, jsonData.oneMoreStep, jsonData.fileId);
                    } else {
                        if (jsonData.totalCount == 0) {
                            updateProgression(0, 0, 0, true, moreStep, jsonData.moreStepLabel);
                        } else {
                            updateProgression(total, total, total, true, moreStep, jsonData.moreStepLabel);
                        }
                        location.replace(controller_link + '&action=getFile&id=' + jsonData.fileId + '&type=' + jsonData.type + '&name=' + jsonData.name);
                    }
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
//                if (textStatus == 'parsererror') {
                    textStatus = XMLHttpRequest.responseText;
//                }
                updateProgressionError(textStatus, false);
            }
        });
    }

    $('#export_stop_button').unbind('click').click(function () {
        if (exportContinueRequest) {
            $('#exportProgress').modal('hide');
            exportContinueRequest = false;
            window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
        } else {
            exportCancelRequest = true;
            $('#export_details_progressing').hide();
            $('#export_details_finished').hide();
            $('#export_details_stop').show();
            $('#export_stop_button').hide();
            $('#export_close_button').hide();
        }
    });

    $('#export_continue_button').unbind('click').click(function () {
        $('#export_continue_button').hide();
        exportContinueRequest = false;
        $('#export_progress_div').show();
        $('#export_details_warning ul, #export_details_info ul').html('');
        $('#export_details_warning, #export_details_info').hide();
        exportNow(0, limit, -1, {}, 0);
    });

    function updateProgressionInit() {
        $('#exportProgress').modal({backdrop: 'static', keyboard: false, closable: false});
        $('#exportProgress').modal('show');
        $('#exportProgress').on('hidden.bs.modal', function () {
//        window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
        });

        $('#export_details_progressing').show();
        $('#export_details_finished').hide();
        $('#export_details_error').hide();
        $('#export_details_warning, #export_details_info').hide();
        $('#export_details_stop').hide();
        $('#export_details_post_limit').hide();
        $('#export_details_error ul').html('');
        $('#export_details_warning ul, #export_details_info ul').html('');

//        $('#export_progress_div').hide();
        $('#export_progression_details').html($('#export_progression_details').attr('default-value'));
        $('#export_progressbar_done').width('0%');
        $('#export_progressbar_done').parent().addClass('active progress-striped');
        $('#export_progression_done').html('0');
        $('#export_progressbar_next').width('0%');
        $('#export_progressbar_next').removeClass('progress-bar-danger');
        $('#export_progressbar_next').addClass('progress-bar-success');

        $('#export_stop_button').show();
        $('#export_close_button').hide();
        $('#export_continue_button').hide();
    }

    function updateProgressionInfo(message) {
        $('#export_details_progressing').hide();
        $('#export_details_finished').hide();
        $('#export_details_info ul').append(message);
        $('#export_details_info').show();
    }

    function updateProgressionError(message, forWarnings) {
        $('#export_details_progressing').hide();
        $('#export_details_finished').hide();
        if (forWarnings) {
            $('#export_details_warning ul').append(message);
            $('#export_details_warning').show();
        } else {
            $('#export_details_error ul').append(message);
            $('#export_details_error').show();

            $('#export_progressbar_next').addClass('progress-bar-danger');
            $('#export_progressbar_next').removeClass('progress-bar-success');

            $('#export_stop_button').hide();
            $('#export_close_button').show();
        }
    }

    function updateProgression(currentPosition, total, nextPosition, finish, moreStep, moreStepLabel) {
        if (currentPosition > total)
            currentPosition = total;
        if (nextPosition > total)
            nextPosition = total;

        var progressionDone = currentPosition * 100 / total;
        var progressionNext = nextPosition * 100 / total;

        if (total > 0) {
            $('#export_progress_div').show();
            $('#export_progression_details').html(currentPosition + '/' + total);
            if (moreStep == 0) {
                $('#export_progressbar_done').width(progressionDone + '%');
                $('#export_progression_done').html(parseInt(progressionDone));
                $('#export_progressbar_next').width((progressionNext - progressionDone) + '%');
            } else {
                $('#export_progressbar_next').width('0%');
                $('#export_progressbar_done').width((100 - progressionDone) + '%');
                $('#export_progressbar_done2').width(progressionDone + '%');
                if (moreStepLabel)
                    $('#export_progressbar_done2 span').html(moreStepLabel);
            }
        } else if (total == 0) {
            $('#export_progress_div').show();
            $('#export_progression_details').html('0/0');
            $('#export_progressbar_done').width('100%');
            $('#export_progression_done').html(100);
            $('#export_progressbar_next').width('0%');
        }

        if (finish) {
            $('#export_progressbar_done').parent().removeClass('active progress-striped');
            $('#export_details_post_limit').hide();
            $('#export_details_progressing').hide();
            $('#export_details_finished').show();
            $('#exportProgress').modal({keyboard: true, closable: true});
            $('#export_stop_button').hide();
            $('#export_close_button').show();
        }
    }

    // Enable collapse upon initialization
    $(".collapse").collapse({toggle: false});

    // CSV option is selected
    $('input[name="as"]').change(function () {
        if ($(this).is(':checked')) {
            if ($(this).val() === 'csv') {
                $(this).closest('.catalogEI_form').find('.csv_options').collapse('show');
            } else {
                $(this).closest('.catalogEI_form').find('.csv_options').collapse('hide');
            }
        }
    });

    // Select custom date is selected
    $('.date_collapser').on('change', 'select', function () {
        if ($(this).val() === 'select_date') {
            $(this).closest('.form-group.date_collapser').next().addClass('in');
        } else {
            $(this).closest('.form-group.date_collapser').next().removeClass('in');
        }
    });

    // Check and expand the categories tree
    $('#collapse-all-product_categories_tree').hide();
    $('#product_categories_tree').addClass('full_loaded');
//    $('#product_categories_tree').tree('expandAll');
//    setTimeout(() => {$('#product_categories_tree').tree('expandAll');}, 5000);
    checkAllAssociatedCategories($('#product_categories_tree'));

    $('#collapse-all-category_categories_tree').hide();
    $('#category_categories_tree').addClass('full_loaded');
    checkAllAssociatedCategories($('#category_categories_tree'));
//    $('#category_categories_tree').tree('expandAll');

    $('#collapse-all-combination_categories_tree').hide();
    $('#combination_categories_tree').addClass('full_loaded');
    checkAllAssociatedCategories($('#combination_categories_tree'));

    // When the resfresh button is clicked
    $('.refresh_button').click(function (e) {
        e.preventDefault();
        var table = $(this).attr('id').substr(8);
        eval(table + 'Table').ajax.reload();
    });

    // Product group list - Sub left
    $('#product_columns_group a.list-group-item').click(function (e) {
        e.preventDefault();
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active');

        var id = $(this).attr('id').replace('product_fields_', '');
        if (id === 'all') {
            $('#product_fields .scrollable ul > li').removeClass('hidden');
        } else {
            $('#product_fields .scrollable ul > li').addClass('hidden');
            $('#product_fields .scrollable ul > li.' + id).removeClass('hidden');
        }
    });

    // Sortable
    $('.scrollable ul').sortable({
        cursor: "move",
        axis: "y",
        stop: function (event, ui) {
            ui.item.css('left', '');
        }
    });

    // Datetime picker
    $('input[name="from_date"], input[name="to_date"], \n\
input[name="from_from_date"], input[name="from_to_date"], input[name="to_from_date"], input[name="to_to_date"]').datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: "hh:mm:ss",
        showSecond: true
    });

    // (De)selecting the columns
    $('.catalogEI_form .scrollable ul > li').click(function (e) {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $(this).children('i:not(.icon-info-circle)').removeClass('icon-check-square-o').addClass('icon-square-o');
        } else {
            $(this).addClass('selected');
            $(this).children('i:not(.icon-info-circle)').removeClass('icon-square-o').addClass('icon-check-square-o');
        }
    });

    // Column search
    $('.fields_search').keyup(function (e) {
        var value = $(this).val().toLowerCase();
        if (value) {
            $(this).next('.clearable_clear').show();
        } else {
            $(this).next('.clearable_clear').hide();
        }

        $(this).parents('.scrollable').find('ul > li').each(function () {
            if ($(this).text().toLowerCase().search(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Select all columns
    $('.select_all_columns').click(function (e) {
        e.preventDefault();
        
        var deselected = $(this).closest('.scrollable').find('li:not(.selected)');
        var all = $(this).closest('.scrollable').find('li');
        if (deselected.length != all.length) {
            $(this).children('i:not(.icon-info-circle)').removeClass('icon-check-square-o').addClass('icon-square-o');
            $(this).closest('.scrollable').find('li').removeClass('selected').children('i:not(.icon-info-circle)').removeClass('icon-check-square-o').addClass('icon-square-o');
        } else {
            $(this).children('i:not(.icon-info-circle)').removeClass('icon-square-o').addClass('icon-check-square-o');
            deselected.addClass('selected');
            deselected.children('i:not(.icon-info-circle)').removeClass('icon-square-o').addClass('icon-check-square-o');
        }

    });

    // Rest columns
    $('.reset_columns').click(function (e) {
        e.preventDefault();
        
        $(this).closest('.scrollable').find('li').addClass('selected').children('i:not(.icon-info-circle)').removeClass('icon-square-o').addClass('icon-check-square-o');
        $(this).closest('.scrollable').find('li.additional').removeClass('selected').children('i:not(.icon-info-circle)').removeClass('icon-check-square-o').addClass('icon-square-o');
    });

    // If the "x" icon is clicked in the columns search input
    $('.clearable_clear').click(function () {
        $(this).prev('.fields_search').val('').trigger('keyup');
    });

    // Save changes button is clicked
    $('#save_template_btn').click(function (e) {
        e.preventDefault();
        var name = $('#template_name').val();
        if (!name) {
            return alert(invalid_template_name);
        }

        var config = {}, cols;
        $('.catalogEI form.catalogEI_form').each(function (index, element) {
            cols = {};
            $(element).find('.catalogEI_fields ul > li').each(function (index2, element2) {
                cols[$(element2).data('value')] = [$(element2).text(), $(element2).hasClass('selected')];
            });

            config[$(element).find('input[name=export]').val()] = {
                inputs: decodeURIComponent($(element).serialize()).replace(/\+/g, ' '),
                columns: cols
            };
        });

        // As this takes more space
        if (featuresTable.state()) {
            delete featuresTable.state().checkboxes;
        }

        var dataTables = {};
        for (var prop in filterTables) {
            dataTables[prop] = {
                type: eval(prop).type,
                data: eval(prop).data,
                showWhich: $('#ctrl-show-selected-' + prop).val(),
                columns: eval(prop + 'Table').settings().init().columns.map(obj => ({data: obj.data})),
                order: eval(prop + 'Table').order(),
                search: eval(prop + 'Table').search(),
                pageInfo: eval(prop + 'Table').page.info()
            };
        }
        dataTables = JSON.stringify(dataTables);

        showLoading();
        $.post(controller_link, {ajax_action: 'saveConfig', type: 'dml', ieType: 'export', name: name, config: JSON.stringify(config), datatables: dataTables},
                function (result) {
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                        loadTemplates(json.configs);
                    } else {
                        showErrorMessage(json.message);
                    }
                    $('#template_name').val('');
                    hideLoading();
                });
    });

    // Apply template
    $(document).on('click', '.apply_config', function (e) {
        e.preventDefault();

        uncheckAllAssociatedCategories($('#product_categories_tree'));
        uncheckAllAssociatedCategories($('#category_categories_tree'));
        uncheckAllAssociatedCategories($('#combination_categories_tree'));

        $.each(JSON.parse($(this).parent().parent().attr('data-config')), function (name, value) {
            var pairs = value.inputs.split('&');
            var elem, pair;
            for (pair of pairs) {
                pair = pair.split('=');
                if (pair[0] === 'categories[]') {
                    elem = $('#' + name + '_export_form').find('input[name="' + pair[0] + '"][value="' + pair[1] + '"]');
                    elem.prop("checked", true);
                    elem.parent().addClass("tree-selected");
                } else if ($('[name="' + pair[0] + '"]').attr('type') === 'radio') {
                    $('#' + name + '_export_form').find('input[name="' + pair[0] + '"][value="' + pair[1] + '"]').prop('checked', true);
                } else {
                    $('#' + name + '_export_form').find('[name="' + pair[0] + '"]').val(pair[1]);
                }
            }

            var i = 0, v;
            for (v in value.columns) {
                $("#" + name + "_fields ul > li[data-value='" + v + "']")
                        .toggleClass('selected', value.columns[v][1])
                        .insertBefore($("#" + name + "_fields ul > li:eq(" + i++ + ")"))
                        .children('i:not(.icon-info-circle)')
                        .attr('class', 'icon-' + (value.columns[v][1] ? 'check-' : '') + 'square-o');
            }
        });

        var id = $(this).parent().parent().attr('data-id');
        showLoading();
        if (id == '1') {
            skipAjax = true;
            filterTables = Object.fromEntries(Object.entries(initialData).filter(([key, value]) => key !== 'emails' && key !== 'ftps'));
            for (var prop in filterTables) {
                eval(prop + " = " + '{type: "unselected", data: [], total: 0, filtered: 0}');
                eval(prop + 'Table').search('').order([1, "asc"]).draw();
                eval(prop + 'Table').column(0).checkboxes.select();
                $('#' + prop + '_table_filter input[type=search]').val('');
                $('#' + prop + '_table thead .dt-checkboxes').prop('indeterminate', false);
            }
            skipAjax = false;
            hideLoading();
            showSuccessMessage(template_loaded);
            triggerChanges();
        } else {
            $.get(controller_link, {ajax_action: 'getAllInitialData', id: id},
                    function (response) {
                        hideLoading();
                        response = JSON.parse(response);
                        if (response.message === 'success') {
                            skipAjax = true;
                            filterTables = response.entities;
                            for (var prop in response.entities) {
                                eval(prop).type = response.entities[prop].type;
                                eval(prop).data = response.entities[prop].checkboxes;
                                eval(prop).total = response.entities[prop].recordsTotal;
                                eval(prop + 'Table').page.len(response.entities[prop].pageInfo.length)
                                        .search(response.entities[prop].search)
                                        .order(response.entities[prop].order)
                                        .page(response.entities[prop].pageInfo.start / response.entities[prop].pageInfo.length)
                                        .draw(false);
                                if (eval(prop).data.length !== 0 && parseInt(eval(prop).total) !== eval(prop).data.length) {
                                    $('#' + prop + '_table thead .dt-checkboxes').prop('indeterminate', true);
                                }
                            }
                            skipAjax = false;
                            showSuccessMessage(template_loaded);
                        } else {
                            showErrorMessage(response.message);
                        }
                    });
        }
    });

    // Click trash
    $(document).on('click', '#configs button.delete_file', function (e) {
        if (confirm(sure_to_delete + ' "' + $(this).parent().parent().find('b').text() + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteConfig', type: 'dml', ieType: 'export', id: $(this).parent().parent().attr('data-id')},
                    function (result) {
                        var json = JSON.parse(result);
                        console.log(json);
                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                        loadTemplates(json.configs);
                        hideLoading();
                    });
        }
    });

    $('#import_button').click(function (e) {
        e.preventDefault();
        $('#file').click();
    });

    $(document).on('change', '#file', function () {
        showLoading();
        var files = document.getElementById("file").files;
        var form_data = new FormData();
        for (var i = 0; i < files.length; i++) {
            form_data.append("file[]", files[i]);
        }
        $.ajax({
            url: controller_link + '&ajax=1&action=importSetting',
            type: 'POST',
            data: form_data,
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                var json = JSON.parse(response);
                if (json.type === 'success') {
                    showSuccessMessage(json.message);
                } else {
                    showErrorMessage(json.message);
                }
                loadTemplates(json.configs);
                $('#fader, #spinner').css('display', 'none');
            },
            error: function (response) {
                hideLoading();
            }
        });
    });

    // Schedule collapse
    $('#schedule_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule').collapse("show");
            updateSchedule('IPE_SCHDL_ENABLE', $('input[name="schedule"]:checked').val());
        }
    });
    $('#schedule_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule').collapse("hide");
            updateSchedule('IPE_SCHDL_ENABLE', $('input[name="schedule"]:checked').val());
        }
    });

    $('#schedule_use_email_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-email').collapse("show");
            updateSchedule('IPE_SCHDL_USE_EMAIL', $('input[name="schedule_use_email"]:checked').val());
        }
    });
    $('#schedule_use_email_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-email').collapse("hide");
            updateSchedule('IPE_SCHDL_USE_EMAIL', $('input[name="schedule_use_email"]:checked').val());
        }
    });

    $('#schedule_use_ftp_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("show");
            updateSchedule('IPE_SCHDL_USE_FTP', $('input[name="schedule_use_ftp"]:checked').val());
        }
    });
    $('#schedule_use_ftp_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("hide");
            updateSchedule('IPE_SCHDL_USE_FTP', $('input[name="schedule_use_ftp"]:checked').val());
        }
    });

    $('#schedule_dont_send_empty_yes').change(function () {
        if ($(this).prop('checked')) {
            updateSchedule('IPE_SCHDL_DNSEM', $('input[name="schedule_dont_send_empty"]:checked').val());
        }
    });
    $('#schedule_dont_send_empty_no').change(function () {
        if ($(this).prop('checked')) {
            updateSchedule('IPE_SCHDL_DNSEM', $('input[name="schedule_dont_send_empty"]:checked').val());
        }
    });

    // Datatables
    var filterTables = Object.fromEntries(Object.entries(initialData).filter(([key, value]) => key !== 'emails' && key !== 'ftps'));

    var skipAjax = false; // flag to use fake AJAX
    if (typeof setProductsTable !== 'undefined') {
        function updateProductsSelectInfo() {
            $('#products_select_info').text(products.type === 'selected' ? products.data.length + ' rows selected' : products.total - products.data.length + ' rows selected');

        }

        var products = {type: "unselected", data: [], total: initialData.products.recordsTotal, filtered: initialData.products.recordsFiltered};
        var allProductsClicked = false;

        var productsTable = $('#products_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.products.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            deferLoading: initialData.products.recordsTotal,
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getProducts';
                    d.extra_search_type = $('#ctrl-show-selected-products').val();
                    d.extra_search_params = {type: products.type, data: products.data};

                    if (skipAjax) { //if fake AJAX flag is set
                        filterTables.products.draw = d.draw; //get draw value to be sent to server
                    }
                },
                beforeSend: function (jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server
                    if (skipAjax) { //if fake AJAX flag is set
                        this.success(filterTables.products); //call success function of current AJAX object (while passing fake data) which is used by DataTable on successful response from server

                        return false; //cancel current AJAX request
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "image"},
                {data: "name"},
                {data: "reference"},
                {data: "category"},
                {data: "base_price"},
                {data: "final_price"},
                {data: "quantity"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#products_select_info').length === 0) {
                    $('#products_table_info').append('<span id="products_select_info" class="select_info"></span>');
                }
                updateProductsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
//                api.rows({page: 'all'}).select();
//                api.rows().select();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {
                            if (Array.isArray(nodes.selector.rows)) {
                                var index;
                                if (products.type === 'unselected') {
                                    index = products.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            products.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            products.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = products.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            products.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            products.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateProductsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allProductsClicked) {
                                if (selected) {
                                    products.type = 'unselected';
                                } else {
                                    products.type = 'selected';
                                }
                                products.data = [];
                                allProductsClicked = false;
                            }
                            updateProductsSelectInfo();

                            if (!indeterminate && products.data.length !== 0 && parseInt(products.total) !== products.data.length) {
                                $('#products_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (products.type === 'unselected') {
                            if (products.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (products.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
                    width: "80px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center"
                },
                {
                    targets: 7,
                    className: "dt-center"
                },
                {
                    targets: 8,
                    className: "dt-center"
                },
                {
                    targets: 9,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        productsTable.on('xhr', function () {
            var json = productsTable.ajax.json();
            products.total = json.recordsTotal;
        });

        $('#products_table th.dt-checkboxes-select-all').click(function () {
            allProductsClicked = true;
        });
        $('#ctrl-show-selected-products').on('change', function () {
            productsTable.ajax.reload();
        });

        var collectProducts = function () {
            $('#product_export_form input[name="products_type"]').remove();
            $('#product_export_form input[name="products_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "products_type").val(products.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "products_data").val(products.data));
        };
    }
    
    if (typeof setCombinationsTable !== 'undefined') {
        function updateCombinationsSelectInfo() {
            $('#combinations_select_info').text(combinations.type === 'selected' ? combinations.data.length + ' rows selected' : combinations.total - combinations.data.length + ' rows selected');

        }

        var combinations = {type: "unselected", data: [], total: initialData.combinations.recordsTotal, filtered: initialData.combinations.recordsFiltered};
        var allCombinationsClicked = false;

        var combinationsTable = $('#combinations_table').DataTable({
            order: [[1, 'asc'], [5, 'asc']],
            data: initialData.combinations.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            deferLoading: initialData.combinations.recordsTotal,
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCombinations';
                    d.extra_search_type = $('#ctrl-show-selected-combinations').val();
                    d.extra_search_params = {type: combinations.type, data: combinations.data};

                    if (skipAjax) { //if fake AJAX flag is set
                        filterTables.combinations.draw = d.draw; //get draw value to be sent to server
                    }
                },
                beforeSend: function (jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server
                    if (skipAjax) { //if fake AJAX flag is set
                        this.success(filterTables.combinations); //call success function of current AJAX object (while passing fake data) which is used by DataTable on successful response from server

                        return false; //cancel current AJAX request
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "combination_reference"},
                {data: "combination"},
                {data: "combination_quantity"},
                {data: "product_id"},
                {data: "product_image"},
                {data: "product_name"},
                {data: "product_reference"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#combinations_select_info').length === 0) {
                    $('#combinations_table_info').append('<span id="combinations_select_info" class="select_info"></span>');
                }
                updateCombinationsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
//                api.rows({page: 'all'}).select();
//                api.rows().select();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {
                            if (Array.isArray(nodes.selector.rows)) {
                                var index;
                                if (combinations.type === 'unselected') {
                                    index = combinations.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            combinations.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            combinations.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = combinations.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            combinations.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            combinations.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCombinationsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCombinationsClicked) {
                                if (selected) {
                                    combinations.type = 'unselected';
                                } else {
                                    combinations.type = 'selected';
                                }
                                combinations.data = [];
                                allCombinationsClicked = false;
                            }
                            updateCombinationsSelectInfo();

                            if (!indeterminate && combinations.data.length !== 0 && parseInt(combinations.total) !== combinations.data.length) {
                                $('#combinations_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (combinations.type === 'unselected') {
                            if (combinations.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (combinations.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "90px",
                },
                {
                    targets: 5,
                    width: "80px",
                    className: "dt-center",
                },
                {
                    targets: 6,
                    width: "80px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: [1, 4],
                    className: "dt-center"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        combinationsTable.on('xhr', function () {
            var json = combinationsTable.ajax.json();
            combinations.total = json.recordsTotal;
        });

        $('#combinations_table th.dt-checkboxes-select-all').click(function () {
            allCombinationsClicked = true;
        });
        $('#ctrl-show-selected-combinations').on('change', function () {
            combinationsTable.ajax.reload();
        });

        var collectCombinations = function () {
            $('#combination_export_form input[name="combinations_type"]').remove();
            $('#combination_export_form input[name="combinations_data"]').remove();
            $('#combination_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "combinations_type").val(combinations.type));
            $('#combination_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "combinations_data").val(combinations.data));
        };
    }

    if (typeof setAttributesTable !== 'undefined') {
        function updateAttributesSelectInfo() {
            $('#attributes_select_info').text(attributes.type === 'selected' ? attributes.data.length + ' rows selected' : attributes.total - attributes.data.length + ' rows selected');

        }

        var attributes = {type: "unselected", data: [], total: initialData.attributes.recordsTotal, filtered: initialData.attributes.recordsFiltered};
        var allAttributesClicked = false;

        var attributesTable = $('#attributes_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.attributes.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.attributes.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAttributes';
                    d.extra_search_type = $('#ctrl-show-selected-attributes').val();
                    d.extra_search_params = {type: attributes.type, data: attributes.data};
                    if (skipAjax) {
                        filterTables.attributes.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.attributes);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "group_name"},
                {data: "attribute_name"},
                {data: "group_type"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#attributes_select_info').length === 0) {
                    $('#attributes_table_info').append('<span id="attributes_select_info" class="select_info"></span>');
                }
                updateAttributesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (attributes.type === 'unselected') {
                                    index = attributes.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            attributes.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            attributes.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = attributes.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            attributes.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            attributes.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAttributesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAttributesClicked) {
                                if (selected) {
                                    attributes.type = 'unselected';
                                } else {
                                    attributes.type = 'selected';
                                }
                                attributes.data = [];
                                allAttributesClicked = false;
                            }
                            updateAttributesSelectInfo();

                            if (!indeterminate && attributes.data.length !== 0 && parseInt(attributes.total) !== attributes.data.length) {
                                $('#attributes_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (attributes.type === 'unselected') {
                            if (attributes.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (attributes.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        attributesTable.on('xhr', function () {
            var json = attributesTable.ajax.json();
            attributes.total = json.recordsTotal;
        });

        $('#attributes_table th.dt-checkboxes-select-all').click(function () {
            allAttributesClicked = true;
        });
        $('#ctrl-show-selected-attributes').on('change', function () {
            attributesTable.ajax.reload();
        });

        var collectAttributes = function () {
            $('#combination_export_form input[name="attributes_type"]').remove();
            $('#combination_export_form input[name="attributes_data"]').remove();
            $('#combination_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributes_type").val(attributes.type));
            $('#combination_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributes_data").val(attributes.data));
        };
    }

    if (typeof setFeaturesTable !== 'undefined') {
        function updateFeaturesSelectInfo() {
            $('#features_select_info').text(features.type === 'selected' ? features.data.length + ' rows selected' : features.total - features.data.length + ' rows selected');

        }

        var features = {type: "unselected", data: [], total: initialData.features.recordsTotal, filtered: initialData.features.recordsFiltered};
        var allFeaturesClicked = false;

        var featuresTable = $('#features_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.features.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.features.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getFeatures';
                    d.extra_search_type = $('#ctrl-show-selected-features').val();
                    d.extra_search_params = {type: features.type, data: features.data};
                    if (skipAjax) {
                        filterTables.features.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.features);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "feature_name"},
                {data: "feature_value"},
                {data: "custom"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#features_select_info').length === 0) {
                    $('#features_table_info').append('<span id="features_select_info" class="select_info"></span>');
                }
                updateFeaturesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (features.type === 'unselected') {
                                    index = features.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id.replace('"', '&quuot;'));

                                    if (selected) {
                                        if (index > -1) {
                                            features.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            features.data.push(nodes.row(nodes.selector.rows[0]).data().id.replace('"', '&quuot;'));
                                        }
                                    }
                                } else {
                                    index = features.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id.replace('"', '&quuot;'));
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            features.data.push(nodes.row(nodes.selector.rows[0]).data().id.replace('"', '&quuot;'));
                                        }
                                    } else {

                                        if (index > -1) {
                                            features.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateFeaturesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allFeaturesClicked) {
                                if (selected) {
                                    features.type = 'unselected';
                                } else {
                                    features.type = 'selected';
                                }
                                features.data = [];
                                allFeaturesClicked = false;
                            }
                            updateFeaturesSelectInfo();

                            if (!indeterminate && features.data.length !== 0 && parseInt(features.total) !== features.data.length) {
                                $('#features_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (features.type === 'unselected') {
                            if (features.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (features.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 3,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        featuresTable.on('xhr', function () {
            var json = featuresTable.ajax.json();
            features.total = json.recordsTotal;
        });

        $('#features_table th.dt-checkboxes-select-all').click(function () {
            allFeaturesClicked = true;
        });
        $('#ctrl-show-selected-features').on('change', function () {
            featuresTable.ajax.reload();
        });

        var collectFeatures = function () {
            $('#product_export_form input[name="features_type"]').remove();
            $('#product_export_form input[name="features_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "features_type").val(features.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "features_data").val(features.data));
        };
    }

    if (typeof setManufacturersTable !== 'undefined') {
        function updateManufacturersSelectInfo() {
            $('#manufacturers_select_info').text(manufacturers.type === 'selected' ? manufacturers.data.length + ' rows selected' : manufacturers.total - manufacturers.data.length + ' rows selected');

        }

        var manufacturers = {type: "unselected", data: [], total: initialData.manufacturers.recordsTotal, filtered: initialData.manufacturers.recordsFiltered};
        var allManufacturersClicked = false;

        var manufacturersTable = $('#manufacturers_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.manufacturers.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.manufacturers.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getManufacturers';
                    d.extra_search_type = $('#ctrl-show-selected-manufacturers').val();
                    d.extra_search_params = {type: manufacturers.type, data: manufacturers.data};
                    if (skipAjax) {
                        filterTables.manufacturers.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.manufacturers);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "address_count"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#manufacturers_select_info').length === 0) {
                    $('#manufacturers_table_info').append('<span id="manufacturers_select_info" class="select_info"></span>');
                }
                updateManufacturersSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {
                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (manufacturers.type === 'unselected') {
                                    index = manufacturers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            manufacturers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            manufacturers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = manufacturers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            manufacturers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            manufacturers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateManufacturersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allManufacturersClicked) {
                                if (selected) {
                                    manufacturers.type = 'unselected';
                                } else {
                                    manufacturers.type = 'selected';
                                }
                                manufacturers.data = [];
                                allManufacturersClicked = false;
                            }
                            updateManufacturersSelectInfo();

                            if (!indeterminate && manufacturers.data.length !== 0 && parseInt(manufacturers.total) !== manufacturers.data.length) {
                                $('#manufacturers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (manufacturers.type === 'unselected') {
                            if (manufacturers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (manufacturers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    orderable: false,
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 6,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        manufacturersTable.on('xhr', function () {
            var json = manufacturersTable.ajax.json();
            manufacturers.total = json.recordsTotal;
        });

        $('#manufacturers_table th.dt-checkboxes-select-all').click(function () {
            allManufacturersClicked = true;
        });
        $('#ctrl-show-selected-manufacturers').on('change', function () {
            manufacturersTable.ajax.reload();
        });

        var collectManufacturers = function () {
            $('#product_export_form input[name="manufacturers_type"]').remove();
            $('#product_export_form input[name="manufacturers_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "manufacturers_type").val(manufacturers.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "manufacturers_data").val(manufacturers.data));
        };
    }

    if (typeof setDiscountsTable !== 'undefined') {
        function updateDiscountsSelectInfo() {
            $('#discounts_select_info').text(discounts.type === 'selected' ? discounts.data.length + ' rows selected' : discounts.total - discounts.data.length + ' rows selected');

        }

        var discounts = {type: "unselected", data: [], total: initialData.discounts.recordsTotal, filtered: initialData.discounts.recordsFiltered};
        var allDiscountsClicked = false;

        var discountsTable = $('#discounts_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.discounts.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.discounts.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getDiscounts';
                    d.extra_search_type = $('#ctrl-show-selected-discounts').val();
                    d.extra_search_params = {type: discounts.type, data: discounts.data};
                    if (skipAjax) {
                        filterTables.discounts.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.discounts);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "spr_name"},
                {data: "prod_name"},
                {data: "reduction"},
                {data: "from_to"},
                {data: "price"},
                {data: "from_quantity"},
                {data: "group_name"},
                {data: "is_rule"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#discounts_select_info').length === 0) {
                    $('#discounts_table_info').append('<span id="discounts_select_info" class="select_info"></span>');
                }
                updateDiscountsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (discounts.type === 'unselected') {
                                    index = discounts.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            discounts.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            discounts.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = discounts.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            discounts.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            discounts.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateDiscountsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allDiscountsClicked) {
                                if (selected) {
                                    discounts.type = 'unselected';
                                } else {
                                    discounts.type = 'selected';
                                }
                                discounts.data = [];
                                allDiscountsClicked = false;
                            }
                            updateDiscountsSelectInfo();

                            if (!indeterminate && discounts.data.length !== 0 && parseInt(discounts.total) !== discounts.data.length) {
                                $('#discounts_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (discounts.type === 'unselected') {
                            if (discounts.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (discounts.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
//                {
//                    targets: 1,
//                    width: "70px"
//                },
                {
                    targets: 1,
//                    width: "30%",
                    orderable: false,
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return data ? data : '--';
                    }
                },
                {
                    targets: 7,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 8,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        discountsTable.on('xhr', function () {
            var json = discountsTable.ajax.json();
            discounts.total = json.recordsTotal;
        });

        $('#discounts_table th.dt-checkboxes-select-all').click(function () {
            allDiscountsClicked = true;
        });
        $('#ctrl-show-selected-discounts').on('change', function () {
            discountsTable.ajax.reload();
        });

        var collectDiscounts = function () {
            $('#discount_export_form input[name="discounts_type"]').remove();
            $('#discount_export_form input[name="discounts_data"]').remove();
            $('#discount_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "discounts_type").val(discounts.type));
            $('#discount_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "discounts_data").val(discounts.data));
        };
    }

    if (typeof setBrandsTable !== 'undefined') {
        function updateBrandsSelectInfo() {
            $('#brands_select_info').text(brands.type === 'selected' ? brands.data.length + ' rows selected' : brands.total - brands.data.length + ' rows selected');

        }

        var brands = {type: "unselected", data: [], total: initialData.brands.recordsTotal, filtered: initialData.brands.recordsFiltered};
        var allBrandsClicked = false;

        var brandsTable = $('#brands_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.brands.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.brands.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getManufacturers';
                    d.extra_search_type = $('#ctrl-show-selected-brands').val();
                    d.extra_search_params = {type: brands.type, data: brands.data};
                    if (skipAjax) {
                        filterTables.brands.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.brands);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "address_count"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#brands_select_info').length === 0) {
                    $('#brands_table_info').append('<span id="brands_select_info" class="select_info"></span>');
                }
                updateBrandsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (brands.type === 'unselected') {
                                    index = brands.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            brands.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            brands.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = brands.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            brands.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            brands.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateBrandsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allBrandsClicked) {
                                if (selected) {
                                    brands.type = 'unselected';
                                } else {
                                    brands.type = 'selected';
                                }
                                brands.data = [];
                                allBrandsClicked = false;
                            }
                            updateBrandsSelectInfo();

                            if (!indeterminate && brands.data.length !== 0 && parseInt(brands.total) !== brands.data.length) {
                                $('#brands_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (brands.type === 'unselected') {
                            if (brands.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (brands.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    orderable: false,
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 6,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        brandsTable.on('xhr', function () {
            var json = brandsTable.ajax.json();
            brands.total = json.recordsTotal;
        });

        $('#brands_table th.dt-checkboxes-select-all').click(function () {
            allBrandsClicked = true;
        });
        $('#ctrl-show-selected-brands').on('change', function () {
            brandsTable.ajax.reload();
        });

        var collectBrands = function () {
            $('#brand_export_form input[name="brands_type"]').remove();
            $('#brand_export_form input[name="brands_data"]').remove();
            $('#brand_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "brands_type").val(brands.type));
            $('#brand_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "brands_data").val(brands.data));
        };
    }

    if (typeof setWarehousesTable !== 'undefined') {
        function updateWarehousesSelectInfo() {
            $('#warehouses_select_info').text(warehouses.type === 'selected' ? warehouses.data.length + ' rows selected' : warehouses.total - warehouses.data.length + ' rows selected');

        }

        var warehouses = {type: "unselected", data: [], total: initialData.warehouses.recordsTotal, filtered: initialData.warehouses.recordsFiltered};
        var allWarehousesClicked = false;

        var warehousesTable = $('#warehouses_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.warehouses.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.warehouses.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getWarehouses';
                    d.extra_search_type = $('#ctrl-show-selected-warehouses').val();
                    d.extra_search_cparams = {type: warehouses.type, data: warehouses.data};
                    if (skipAjax) {
                        filterTables.warehouses.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.warehouses);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "reference"},
                {data: "name"},
                {data: "management_type"},
                {data: "iso_code"},
                {data: "address1"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#warehouses_select_info').length === 0) {
                    $('#warehouses_table_info').append('<span id="warehouses_select_info" class="select_info"></span>');
                }
                updateWarehousesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (warehouses.type === 'unselected') {
                                    index = warehouses.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            warehouses.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            warehouses.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = warehouses.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            warehouses.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            warehouses.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateWarehousesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allWarehousesClicked) {
                                if (selected) {
                                    warehouses.type = 'unselected';
                                } else {
                                    warehouses.type = 'selected';
                                }
                                warehouses.data = [];
                                allWarehousesClicked = false;
                            }
                            updateWarehousesSelectInfo();

                            if (!indeterminate && warehouses.data.length !== 0 && parseInt(warehouses.total) !== warehouses.data.length) {
                                $('#warehouses_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (warehouses.type === 'unselected') {
                            if (warehouses.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (warehouses.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        warehousesTable.on('xhr', function () {
            var json = warehousesTable.ajax.json();
            warehouses.total = json.recordsTotal;
        });

        $('#warehouses_table th.dt-checkboxes-select-all').click(function () {
            allWarehousesClicked = true;
        });
        $('#ctrl-show-selected-warehouses').on('change', function () {
            warehousesTable.ajax.reload();
        });

        var collectWarehouses = function () {
            $('#warehouse_export_form input[name="warehouses_type"]').remove();
            $('#warehouse_export_form input[name="warehouses_data"]').remove();
            $('#warehouse_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "warehouses_type").val(warehouses.type));
            $('#warehouse_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "warehouses_data").val(warehouses.data));
        };
    }
    
    if (typeof setPacksTable !== 'undefined') {
        function updatePacksSelectInfo() {
            $('#packs_select_info').text(packs.type === 'selected' ? packs.data.length + ' rows selected' : packs.total - packs.data.length + ' rows selected');

        }

        var packs = {type: "unselected", data: [], total: initialData.packs.recordsTotal, filtered: initialData.packs.recordsFiltered};
        var allPacksClicked = false;

        var packsTable = $('#packs_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.packs.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.packs.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getPacks';
                    d.extra_search_type = $('#ctrl-show-selected-packs').val();
                    d.extra_search_cparams = {type: packs.type, data: packs.data};
                    if (skipAjax) {
                        filterTables.packs.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.packs);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "pack_name"},
                {data: "product_id"},
                {data: "product_name"},
                {data: "product_reference"},
                {data: "combination_reference"},
                {data: "name_values"},
                {data: "quantity"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#packs_select_info').length === 0) {
                    $('#packs_table_info').append('<span id="packs_select_info" class="select_info"></span>');
                }
                updatePacksSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (packs.type === 'unselected') {
                                    index = packs.data.indexOf(nodes.row(nodes.selector.rows[0]).data().unique_id);

                                    if (selected) {
                                        if (index > -1) {
                                            packs.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            packs.data.push(nodes.row(nodes.selector.rows[0]).data().unique_id);
                                        }
                                    }
                                } else {
                                    index = packs.data.indexOf(nodes.row(nodes.selector.rows[0]).data().unique_id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            packs.data.push(nodes.row(nodes.selector.rows[0]).data().unique_id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            packs.data.splice(index, 1);
                                        }
                                    }
                                }
                                updatePacksSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allPacksClicked) {
                                if (selected) {
                                    packs.type = 'unselected';
                                } else {
                                    packs.type = 'selected';
                                }
                                packs.data = [];
                                allPacksClicked = false;
                            }
                            updatePacksSelectInfo();

                            if (!indeterminate && packs.data.length !== 0 && parseInt(packs.total) !== packs.data.length) {
                                $('#packs_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (packs.type === 'unselected') {
                            if (packs.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (packs.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    width: "110px",
                    className: "dt-center"
                },
                {
                    targets: 6,
                    width: "200px",
                    className: "dt-center"
                },
                {
                    targets: 8,
                    width: "90px",
                    className: "dt-center"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        packsTable.on('xhr', function () {
            var json = packsTable.ajax.json();
            packs.total = json.recordsTotal;
        });

        $('#packs_table th.dt-checkboxes-select-all').click(function () {
            allPacksClicked = true;
        });
        $('#ctrl-show-selected-packs').on('change', function () {
            packsTable.ajax.reload();
        });

        var collectPacks = function () {
            $('#pack_export_form input[name="packs_type"]').remove();
            $('#pack_export_form input[name="packs_data"]').remove();
            $('#pack_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "packs_type").val(packs.type));
            $('#pack_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "packs_data").val(packs.data));
        };
    }

    if (typeof setAliasesTable !== 'undefined') {
        function updateAliasesSelectInfo() {
            $('#aliases_select_info').text(aliases.type === 'selected' ? aliases.data.length + ' rows selected' : aliases.total - aliases.data.length + ' rows selected');

        }

        var aliases = {type: "unselected", data: [], total: initialData.aliases.recordsTotal, filtered: initialData.aliases.recordsFiltered};
        var allAliasesClicked = false;

        var aliasesTable = $('#aliases_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.aliases.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.aliases.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAliases';
                    d.extra_search_type = $('#ctrl-show-selected-aliases').val();
                    d.extra_search_params = {type: aliases.type, data: aliases.data};
                    if (skipAjax) {
                        filterTables.aliases.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.aliases);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "alias"},
                {data: "search"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#aliases_select_info').length === 0) {
                    $('#aliases_table_info').append('<span id="aliases_select_info" class="select_info"></span>');
                }
                updateAliasesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (aliases.type === 'unselected') {
                                    index = aliases.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            aliases.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            aliases.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = aliases.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            aliases.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            aliases.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAliasesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAliasesClicked) {
                                if (selected) {
                                    aliases.type = 'unselected';
                                } else {
                                    aliases.type = 'selected';
                                }
                                aliases.data = [];
                                allAliasesClicked = false;
                            }
                            updateAliasesSelectInfo();

                            if (!indeterminate && aliases.data.length !== 0 && parseInt(aliases.total) !== aliases.data.length) {
                                $('#aliases_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (aliases.type === 'unselected') {
                            if (aliases.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (aliases.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 4,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        aliasesTable.on('xhr', function () {
            var json = aliasesTable.ajax.json();
            aliases.total = json.recordsTotal;
        });

        $('#aliases_table th.dt-checkboxes-select-all').click(function () {
            allAliasesClicked = true;
        });
        $('#ctrl-show-selected-aliases').on('change', function () {
            aliasesTable.ajax.reload();
        });

        var collectAliases = function () {
            $('#alias_export_form input[name="aliases_type"]').remove();
            $('#alias_export_form input[name="aliases_data"]').remove();
            $('#alias_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "aliases_type").val(aliases.type));
            $('#alias_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "aliases_data").val(aliases.data));
        };
    }

    if (typeof setSuppliersTable !== 'undefined') {
        function updateSuppliersSelectInfo() {
            $('#suppliers_select_info').text(suppliers.type === 'selected' ? suppliers.data.length + ' rows selected' : suppliers.total - suppliers.data.length + ' rows selected');

        }

        var suppliers = {type: "unselected", data: [], total: initialData.suppliers.recordsTotal, filtered: initialData.suppliers.recordsFiltered};
        var allSuppliersClicked = false;

        var suppliersTable = $('#suppliers_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.suppliers.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.suppliers.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getSuppliers';
                    d.extra_search_type = $('#ctrl-show-selected-suppliers').val();
                    d.extra_search_params = {type: suppliers.type, data: suppliers.data};
                    if (skipAjax) {
                        filterTables.suppliers.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.suppliers);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#suppliers_select_info').length === 0) {
                    $('#suppliers_table_info').append('<span id="suppliers_select_info" class="select_info"></span>');
                }
                updateSuppliersSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (suppliers.type === 'unselected') {
                                    index = suppliers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            suppliers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            suppliers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = suppliers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            suppliers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            suppliers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateSuppliersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allSuppliersClicked) {
                                if (selected) {
                                    suppliers.type = 'unselected';
                                } else {
                                    suppliers.type = 'selected';
                                }
                                suppliers.data = [];
                                allSuppliersClicked = false;
                            }
                            updateSuppliersSelectInfo();

                            if (!indeterminate && suppliers.data.length !== 0 && parseInt(suppliers.total) !== suppliers.data.length) {
                                $('#suppliers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (suppliers.type === 'unselected') {
                            if (suppliers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (suppliers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        suppliersTable.on('xhr', function () {
            var json = suppliersTable.ajax.json();
            suppliers.total = json.recordsTotal;
        });

        $('#suppliers_table th.dt-checkboxes-select-all').click(function () {
            allSuppliersClicked = true;
        });
        $('#ctrl-show-selected-suppliers').on('change', function () {
            suppliersTable.ajax.reload();
        });

        var collectSuppliers = function () {
            $('#product_export_form input[name="suppliers_type"]').remove();
            $('#product_export_form input[name="suppliers_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers_type").val(suppliers.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers_data").val(suppliers.data));
        };
    }

    if (typeof setStoresTable !== 'undefined') {
        function updateStoresSelectInfo() {
            $('#stores_select_info').text(stores.type === 'selected' ? stores.data.length + ' rows selected' : stores.total - stores.data.length + ' rows selected');

        }

        var stores = {type: "unselected", data: [], total: initialData.stores.recordsTotal, filtered: initialData.stores.recordsFiltered};
        var allStoresClicked = false;

        var storesTable = $('#stores_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.stores.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.stores.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getStores';
                    d.extra_search_type = $('#ctrl-show-selected-stores').val();
                    d.extra_search_params = {type: stores.type, data: stores.data};
                    if (skipAjax) {
                        filterTables.stores.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.stores);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "address1"},
                {data: "city"},
                {data: "postcode"},
                {data: "state_name"},
                {data: "country_name"},
                {data: "phone"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#stores_select_info').length === 0) {
                    $('#stores_table_info').append('<span id="stores_select_info" class="select_info"></span>');
                }
                updateStoresSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (stores.type === 'unselected') {
                                    index = stores.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            stores.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            stores.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = stores.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            stores.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            stores.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateStoresSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allStoresClicked) {
                                if (selected) {
                                    stores.type = 'unselected';
                                } else {
                                    stores.type = 'selected';
                                }
                                stores.data = [];
                                allStoresClicked = false;
                            }
                            updateStoresSelectInfo();

                            if (!indeterminate && stores.data.length !== 0 && parseInt(stores.total) !== stores.data.length) {
                                $('#stores_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (stores.type === 'unselected') {
                            if (stores.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (stores.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
//                {
//                    targets: 4,
//                    width: "90px",
//                    className: "dt-center",
//                    render: function (data, type, row, meta) {
//                        return parseInt(data) ? data : '--';
//                    }
//                },
                {
                    targets: 10,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        storesTable.on('xhr', function () {
            var json = storesTable.ajax.json();
            stores.total = json.recordsTotal;
        });

        $('#stores_table th.dt-checkboxes-select-all').click(function () {
            allStoresClicked = true;
        });
        $('#ctrl-show-selected-stores').on('change', function () {
            storesTable.ajax.reload();
        });

        var collectStores = function () {
            $('#store_export_form input[name="stores_type"]').remove();
            $('#store_export_form input[name="stores_data"]').remove();
            $('#store_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "stores_type").val(stores.type));
            $('#store_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "stores_data").val(stores.data));
        };
    }

    if (typeof setSuppliers2Table !== 'undefined') {
        function updateSuppliers2SelectInfo() {
            $('#suppliers2_select_info').text(suppliers2.type === 'selected' ? suppliers2.data.length + ' rows selected' : suppliers2.total - suppliers2.data.length + ' rows selected');

        }

        var suppliers2 = {type: "unselected", data: [], total: initialData.suppliers2.recordsTotal, filtered: initialData.suppliers2.recordsFiltered};
        var allSuppliers2Clicked = false;

        var suppliers2Table = $('#suppliers2_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.suppliers2.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.suppliers2.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getSuppliers';
                    d.extra_search_type = $('#ctrl-show-selected-suppliers2').val();
                    d.extra_search_params = {type: suppliers2.type, data: suppliers2.data};
                    if (skipAjax) {
                        filterTables.suppliers2.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.suppliers2);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#suppliers2_select_info').length === 0) {
                    $('#suppliers2_table_info').append('<span id="suppliers2_select_info" class="select_info"></span>');
                }
                updateSuppliers2SelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (suppliers2.type === 'unselected') {
                                    index = suppliers2.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            suppliers2.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            suppliers2.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = suppliers2.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            suppliers2.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            suppliers2.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateSuppliers2SelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allSuppliers2Clicked) {
                                if (selected) {
                                    suppliers2.type = 'unselected';
                                } else {
                                    suppliers2.type = 'selected';
                                }
                                suppliers2.data = [];
                                allSuppliers2Clicked = false;
                            }
                            updateSuppliers2SelectInfo();

                            if (!indeterminate && suppliers2.data.length !== 0 && parseInt(suppliers2.total) !== suppliers2.data.length) {
                                $('#suppliers2_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (suppliers2.type === 'unselected') {
                            if (suppliers2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (suppliers2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        suppliers2Table.on('xhr', function () {
            var json = suppliers2Table.ajax.json();
            suppliers2.total = json.recordsTotal;
        });

        $('#suppliers2_table th.dt-checkboxes-select-all').click(function () {
            allSuppliers2Clicked = true;
        });
        $('#ctrl-show-selected-suppliers2').on('change', function () {
            suppliers2Table.ajax.reload();
        });

        var collectSuppliers2 = function () {
            $('#supplier_export_form input[name="suppliers2_type"]').remove();
            $('#supplier_export_form input[name="suppliers2_data"]').remove();
            $('#supplier_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers2_type").val(suppliers2.type));
            $('#supplier_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers2_data").val(suppliers2.data));
        };
    }

    if (typeof setCarriersTable !== 'undefined') {
        function updateCarriersSelectInfo() {
            $('#carriers_select_info').text(carriers.type === 'selected' ? carriers.data.length + ' rows selected' : carriers.total - carriers.data.length + ' rows selected');

        }

        var carriers = {type: "unselected", data: [], total: initialData.carriers.recordsTotal, filtered: initialData.carriers.recordsFiltered};
        var allCarriersClicked = false;

        var carriersTable = $('#carriers_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.carriers.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.carriers.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCarriers';
                    d.extra_search_type = $('#ctrl-show-selected-carriers').val();
                    d.extra_search_params = {type: carriers.type, data: carriers.data};
                    if (skipAjax) {
                        filterTables.carriers.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.carriers);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "reference"},
                {data: "id"},
                {data: "reference"},
                {data: "name"},
                {data: "logo"},
                {data: "delay"},
                {data: "enabled"},
                {data: "is_free"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#carriers_select_info').length === 0) {
                    $('#carriers_table_info').append('<span id="carriers_select_info" class="select_info"></span>');
                }
                updateCarriersSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (carriers.type === 'unselected') {
                                    index = carriers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);

                                    if (selected) {
                                        if (index > -1) {
                                            carriers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            carriers.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    }
                                } else {
                                    index = carriers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            carriers.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    } else {

                                        if (index > -1) {
                                            carriers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCarriersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCarriersClicked) {
                                if (selected) {
                                    carriers.type = 'unselected';
                                } else {
                                    carriers.type = 'selected';
                                }
                                carriers.data = [];
                                allCarriersClicked = false;
                            }
                            updateCarriersSelectInfo();

                            if (!indeterminate && carriers.data.length !== 0 && parseInt(carriers.total) !== carriers.data.length) {
                                $('#carriers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (carriers.type === 'unselected') {
                            if (carriers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (carriers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
                    width: "70px"
                },
                {
                    targets: 4,
//                    width: "90px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                },
                {
                    targets: 7,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        carriersTable.on('xhr', function () {
            var json = carriersTable.ajax.json();
            carriers.total = json.recordsTotal;
        });

        $('#carriers_table th.dt-checkboxes-select-all').click(function () {
            allCarriersClicked = true;
        });
        $('#ctrl-show-selected-carriers').on('change', function () {
            carriersTable.ajax.reload();
        });

        var collectCarriers = function () {
            $('#product_export_form input[name="carriers_type"]').remove();
            $('#product_export_form input[name="carriers_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers_type").val(carriers.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers_data").val(carriers.data));
        };
    }

    if (typeof setCarriers2Table !== 'undefined') {
        function updateCarriers2SelectInfo() {
            $('#carriers2_select_info').text(carriers2.type === 'selected' ? carriers2.data.length + ' rows selected' : carriers2.total - carriers2.data.length + ' rows selected');

        }

        var carriers2 = {type: "unselected", data: [], total: initialData.carriers2.recordsTotal, filtered: initialData.carriers2.recordsFiltered};
        var allCarriers2Clicked = false;

        var carriers2Table = $('#carriers2_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.carriers2.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.carriers2.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCarriers';
                    d.extra_search_type = $('#ctrl-show-selected-carriers2').val();
                    d.extra_search_params = {type: carriers2.type, data: carriers2.data};
                    if (skipAjax) {
                        filterTables.carriers2.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.carriers2);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "reference"},
                {data: "id"},
                {data: "reference"},
                {data: "name"},
                {data: "logo"},
                {data: "delay"},
                {data: "enabled"},
                {data: "is_free"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#carriers2_select_info').length === 0) {
                    $('#carriers2_table_info').append('<span id="carriers2_select_info" class="select_info"></span>');
                }
                updateCarriers2SelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (carriers2.type === 'unselected') {
                                    index = carriers2.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);

                                    if (selected) {
                                        if (index > -1) {
                                            carriers2.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            carriers2.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    }
                                } else {
                                    index = carriers2.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            carriers2.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    } else {

                                        if (index > -1) {
                                            carriers2.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCarriers2SelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCarriers2Clicked) {
                                if (selected) {
                                    carriers2.type = 'unselected';
                                } else {
                                    carriers2.type = 'selected';
                                }
                                carriers2.data = [];
                                allCarriers2Clicked = false;
                            }
                            updateCarriers2SelectInfo();

                            if (!indeterminate && carriers2.data.length !== 0 && parseInt(carriers2.total) !== carriers2.data.length) {
                                $('#carriers2_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (carriers2.type === 'unselected') {

                            if (carriers2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (carriers2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
                    width: "70px"
                },
                {
                    targets: 4,
//                    width: "90px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                },
                {
                    targets: 7,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        carriers2Table.on('xhr', function () {
            var json = carriers2Table.ajax.json();
            carriers2.total = json.recordsTotal;
        });

        $('#carriers2_table th.dt-checkboxes-select-all').click(function () {
            allCarriers2Clicked = true;
        });
        $('#ctrl-show-selected-carriers2').on('change', function () {
            carriers2Table.ajax.reload();
        });

        var collectCarriers2 = function () {
            $('#product_export_form input[name="carriers2_type"]').remove();
            $('#product_export_form input[name="carriers2_data"]').remove();
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers2_type").val(carriers2.type));
            $('#product_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers2_data").val(carriers2.data));
        };
    }

    if (typeof setGroupsTable !== 'undefined') {
        function updateGroupsSelectInfo() {
            $('#groups_select_info').text(groups.type === 'selected' ? groups.data.length + ' rows selected' : groups.total - groups.data.length + ' rows selected');

        }

        var groups = {type: "unselected", data: [], total: initialData.groups.recordsTotal, filtered: initialData.groups.recordsFiltered};
        var allGroupsClicked = false;

        var groupsTable = $('#groups_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.groups.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.groups.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getGroups';
                    d.extra_search_type = $('#ctrl-show-selected-groups').val();
                    d.extra_search_params = {type: groups.type, data: groups.data};
                    if (skipAjax) {
                        filterTables.groups.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.groups);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "reduction"},
                {data: "members"},
                {data: "show_prices"},
                {data: "date_add"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#groups_select_info').length === 0) {
                    $('#groups_table_info').append('<span id="groups_select_info" class="select_info"></span>');
                }
                updateGroupsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (groups.type === 'unselected') {
                                    index = groups.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            groups.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            groups.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = groups.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            groups.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            groups.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateGroupsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allGroupsClicked) {
                                if (selected) {
                                    groups.type = 'unselected';
                                } else {
                                    groups.type = 'selected';
                                }
                                groups.data = [];
                                allGroupsClicked = false;
                            }
                            updateGroupsSelectInfo();

                            if (!indeterminate && groups.data.length !== 0 && parseInt(groups.total) !== groups.data.length) {
                                $('#groups_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (groups.type === 'unselected') {
                            if (groups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (groups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
//                    width: "220px",
                    render: function (data, type, row, meta) {
                        return data + '%';
                    }
                },
                {
                    targets: 4,
                    className: "dt-center"

                },
                {
                    targets: 5,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        groupsTable.on('xhr', function () {
            var json = groupsTable.ajax.json();
            groups.total = json.recordsTotal;
        });

        $('#groups_table th.dt-checkboxes-select-all').click(function () {
            allGroupsClicked = true;
        });
        $('#ctrl-show-selected-groups').on('change', function () {
            groupsTable.ajax.reload();
        });

        var collectGroups = function () {
            $('#customer_export_form input[name="groups_type"]').remove();
            $('#customer_export_form input[name="groups_data"]').remove();
            $('#customer_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups_type").val(groups.type));
            $('#customer_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups_data").val(groups.data));
        };
    }

    if (typeof setGroups2Table !== 'undefined') {
        function updateGroups2SelectInfo() {
            $('#groups2_select_info').text(groups2.type === 'selected' ? groups2.data.length + ' rows selected' : groups2.total - groups2.data.length + ' rows selected');

        }

        var groups2 = {type: "unselected", data: [], total: initialData.groups2.recordsTotal, filtered: initialData.groups2.recordsFiltered};
        var allGroups2Clicked = false;

        var groups2Table = $('#groups2_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.groups2.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.groups2.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getGroups';
                    d.extra_search_type = $('#ctrl-show-selected-groups2').val();
                    d.extra_search_params = {type: groups2.type, data: groups2.data};
                    if (skipAjax) {
                        filterTables.groups2.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.groups2);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "reduction"},
                {data: "members"},
                {data: "show_prices"},
                {data: "date_add"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#groups2_select_info').length === 0) {
                    $('#groups2_table_info').append('<span id="groups2_select_info" class="select_info"></span>');
                }
                updateGroups2SelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (groups2.type === 'unselected') {
                                    index = groups2.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            groups2.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            groups2.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = groups2.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            groups2.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            groups2.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateGroups2SelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allGroups2Clicked) {
                                if (selected) {
                                    groups2.type = 'unselected';
                                } else {
                                    groups2.type = 'selected';
                                }
                                groups2.data = [];
                                allGroups2Clicked = false;
                            }
                            updateGroups2SelectInfo();

                            if (!indeterminate && groups2.data.length !== 0 && parseInt(groups2.total) !== groups2.data.length) {
                                $('#groups2_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (groups2.type === 'unselected') {
                            if (groups2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (groups2.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
//                    width: "220px",
                    render: function (data, type, row, meta) {
                        return data + '%';
                    }
                },
                {
                    targets: 4,
                    className: "dt-center"

                },
                {
                    targets: 5,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        groups2Table.on('xhr', function () {
            var json = groups2Table.ajax.json();
            groups2.total = json.recordsTotal;
        });

        $('#groups2_table th.dt-checkboxes-select-all').click(function () {
            allGroups2Clicked = true;
        });
        $('#ctrl-show-selected-groups2').on('change', function () {
            groups2Table.ajax.reload();
        });

        var collectGroups2 = function () {
            $('#group_export_form input[name="groups2_type"]').remove();
            $('#group_export_form input[name="groups2_data"]').remove();
            $('#group_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups2_type").val(groups2.type));
            $('#group_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups2_data").val(groups2.data));
        };
    }
    
    if (typeof setFeaturesForFeaturesTable !== 'undefined') {
        function updateFeaturesForFeaturesSelectInfo() {
            $('#featuresForFeatures_select_info').text(featuresForFeatures.type === 'selected' ? featuresForFeatures.data.length + ' rows selected' : featuresForFeatures.total - featuresForFeatures.data.length + ' rows selected');

        }

        var featuresForFeatures = {type: "unselected", data: [], total: initialData.featuresForFeatures.recordsTotal, filtered: initialData.featuresForFeatures.recordsFiltered};
        var allFeaturesForFeaturesClicked = false;

        var featuresForFeaturesTable = $('#featuresForFeatures_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.featuresForFeatures.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.featuresForFeatures.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getFeaturesForFeatures';
                    d.extra_search_type = $('#ctrl-show-selected-featuresForFeatures').val();
                    d.extra_search_params = {type: featuresForFeatures.type, data: featuresForFeatures.data};
                    if (skipAjax) {
                        filterTables.featuresForFeatures.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.featuresForFeatures);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "position"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#featuresForFeatures_select_info').length === 0) {
                    $('#featuresForFeatures_table_info').append('<span id="featuresForFeatures_select_info" class="select_info"></span>');
                }
                updateFeaturesForFeaturesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (featuresForFeatures.type === 'unselected') {
                                    index = featuresForFeatures.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            featuresForFeatures.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            featuresForFeatures.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = featuresForFeatures.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            featuresForFeatures.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            featuresForFeatures.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateFeaturesForFeaturesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allFeaturesForFeaturesClicked) {
                                if (selected) {
                                    featuresForFeatures.type = 'unselected';
                                } else {
                                    featuresForFeatures.type = 'selected';
                                }
                                featuresForFeatures.data = [];
                                allFeaturesForFeaturesClicked = false;
                            }
                            updateFeaturesForFeaturesSelectInfo();

                            if (!indeterminate && featuresForFeatures.data.length !== 0 && parseInt(featuresForFeatures.total) !== featuresForFeatures.data.length) {
                                $('#featuresForFeatures_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (featuresForFeatures.type === 'unselected') {
                            if (featuresForFeatures.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (featuresForFeatures.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    className: "dt-center"

                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        featuresForFeaturesTable.on('xhr', function () {
            var json = featuresForFeaturesTable.ajax.json();
            featuresForFeatures.total = json.recordsTotal;
        });

        $('#featuresForFeatures_table th.dt-checkboxes-select-all').click(function () {
            allFeaturesForFeaturesClicked = true;
        });
        $('#ctrl-show-selected-featuresForFeatures').on('change', function () {
            featuresForFeaturesTable.ajax.reload();
        });

        var collectFeaturesForFeatures = function () {
            $('#feature_export_form input[name="featuresForFeatures_type"]').remove();
            $('#feature_export_form input[name="featuresForFeatures_data"]').remove();
            $('#feature_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "featuresForFeatures_type").val(featuresForFeatures.type));
            $('#feature_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "featuresForFeatures_data").val(featuresForFeatures.data));
        };
    }
    
    if (typeof setFeatureValuesTable !== 'undefined') {
        function updateFeatureValuesSelectInfo() {
            $('#featureValues_select_info').text(featureValues.type === 'selected' ? featureValues.data.length + ' rows selected' : featureValues.total - featureValues.data.length + ' rows selected');

        }

        var featureValues = {type: "unselected", data: [], total: initialData.featureValues.recordsTotal, filtered: initialData.featureValues.recordsFiltered};
        var allFeatureValuesClicked = false;

        var featureValuesTable = $('#featureValues_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.featureValues.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.featureValues.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getFeatureValues';
                    d.extra_search_type = $('#ctrl-show-selected-featureValues').val();
                    d.extra_search_params = {type: featureValues.type, data: featureValues.data};
                    if (skipAjax) {
                        filterTables.featureValues.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.featureValues);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "value"},
                {data: "custom"},
                {data: "id_feature"},
                {data: "feature_name"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#featureValues_select_info').length === 0) {
                    $('#featureValues_table_info').append('<span id="featureValues_select_info" class="select_info"></span>');
                }
                updateFeatureValuesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (featureValues.type === 'unselected') {
                                    index = featureValues.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            featureValues.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            featureValues.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = featureValues.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            featureValues.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            featureValues.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateFeatureValuesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allFeatureValuesClicked) {
                                if (selected) {
                                    featureValues.type = 'unselected';
                                } else {
                                    featureValues.type = 'selected';
                                }
                                featureValues.data = [];
                                allFeatureValuesClicked = false;
                            }
                            updateFeatureValuesSelectInfo();

                            if (!indeterminate && featureValues.data.length !== 0 && parseInt(featureValues.total) !== featureValues.data.length) {
                                $('#featureValues_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (featureValues.type === 'unselected') {
                            if (featureValues.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (featureValues.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    className: "dt-center"

                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        featureValuesTable.on('xhr', function () {
            var json = featureValuesTable.ajax.json();
            featureValues.total = json.recordsTotal;
        });

        $('#featureValues_table th.dt-checkboxes-select-all').click(function () {
            allFeatureValuesClicked = true;
        });
        $('#ctrl-show-selected-featureValues').on('change', function () {
            featureValuesTable.ajax.reload();
        });

        var collectFeatureValues = function () {
            $('#feature_export_form input[name="featureValues_type"]').remove();
            $('#feature_export_form input[name="featureValues_data"]').remove();
            $('#feature_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "featureValues_type").val(featureValues.type));
            $('#feature_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "featureValues_data").val(featureValues.data));
        };
    }
    
    if (typeof setAttributeGroupsTable !== 'undefined') {
        function updateAttributeGroupsSelectInfo() {
            $('#attributeGroups_select_info').text(attributeGroups.type === 'selected' ? attributeGroups.data.length + ' rows selected' : attributeGroups.total - attributeGroups.data.length + ' rows selected');

        }

        var attributeGroups = {type: "unselected", data: [], total: initialData.attributeGroups.recordsTotal, filtered: initialData.attributeGroups.recordsFiltered};
        var allAttributeGroupsClicked = false;

        var attributeGroupsTable = $('#attributeGroups_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.attributeGroups.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.attributeGroups.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAttributeGroups';
                    d.extra_search_type = $('#ctrl-show-selected-attributeGroups').val();
                    d.extra_search_params = {type: attributeGroups.type, data: attributeGroups.data};
                    if (skipAjax) {
                        filterTables.attributeGroups.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.attributeGroups);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "public_name"},
                {data: "group_type"},
                {data: "position"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#attributeGroups_select_info').length === 0) {
                    $('#attributeGroups_table_info').append('<span id="attributeGroups_select_info" class="select_info"></span>');
                }
                updateAttributeGroupsSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (attributeGroups.type === 'unselected') {
                                    index = attributeGroups.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            attributeGroups.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            attributeGroups.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = attributeGroups.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            attributeGroups.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            attributeGroups.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAttributeGroupsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAttributeGroupsClicked) {
                                if (selected) {
                                    attributeGroups.type = 'unselected';
                                } else {
                                    attributeGroups.type = 'selected';
                                }
                                attributeGroups.data = [];
                                allAttributeGroupsClicked = false;
                            }
                            updateAttributeGroupsSelectInfo();

                            if (!indeterminate && attributeGroups.data.length !== 0 && parseInt(attributeGroups.total) !== attributeGroups.data.length) {
                                $('#attributeGroups_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (attributeGroups.type === 'unselected') {
                            if (attributeGroups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (attributeGroups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    className: "dt-center"

                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        attributeGroupsTable.on('xhr', function () {
            var json = attributeGroupsTable.ajax.json();
            attributeGroups.total = json.recordsTotal;
        });

        $('#attributeGroups_table th.dt-checkboxes-select-all').click(function () {
            allAttributeGroupsClicked = true;
        });
        $('#ctrl-show-selected-attributeGroups').on('change', function () {
            attributeGroupsTable.ajax.reload();
        });

        var collectAttributeGroups = function () {
            $('#attribute_export_form input[name="attributeGroups_type"]').remove();
            $('#attribute_export_form input[name="attributeGroups_data"]').remove();
            $('#attribute_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributeGroups_type").val(attributeGroups.type));
            $('#attribute_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributeGroups_data").val(attributeGroups.data));
        };
    }
    
    if (typeof setAttributeValuesTable !== 'undefined') {
        function updateAttributeValuesSelectInfo() {
            $('#attributeValues_select_info').text(attributeValues.type === 'selected' ? attributeValues.data.length + ' rows selected' : attributeValues.total - attributeValues.data.length + ' rows selected');

        }

        var attributeValues = {type: "unselected", data: [], total: initialData.attributeValues.recordsTotal, filtered: initialData.attributeValues.recordsFiltered};
        var allAttributeValuesClicked = false;

        var attributeValuesTable = $('#attributeValues_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.attributeValues.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.attributeValues.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAttributeValues';
                    d.extra_search_type = $('#ctrl-show-selected-attributeValues').val();
                    d.extra_search_params = {type: attributeValues.type, data: attributeValues.data};
                    if (skipAjax) {
                        filterTables.attributeValues.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.attributeValues);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "color"},
                {data: "position"},
                {data: "id_attribute_group"},
                {data: "attribute_group_name"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#attributeValues_select_info').length === 0) {
                    $('#attributeValues_table_info').append('<span id="attributeValues_select_info" class="select_info"></span>');
                }
                updateAttributeValuesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (attributeValues.type === 'unselected') {
                                    index = attributeValues.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            attributeValues.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            attributeValues.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = attributeValues.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            attributeValues.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            attributeValues.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAttributeValuesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAttributeValuesClicked) {
                                if (selected) {
                                    attributeValues.type = 'unselected';
                                } else {
                                    attributeValues.type = 'selected';
                                }
                                attributeValues.data = [];
                                allAttributeValuesClicked = false;
                            }
                            updateAttributeValuesSelectInfo();

                            if (!indeterminate && attributeValues.data.length !== 0 && parseInt(attributeValues.total) !== attributeValues.data.length) {
                                $('#attributeValues_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (attributeValues.type === 'unselected') {
                            if (attributeValues.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (attributeValues.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    className: "dt-center"

                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        attributeValuesTable.on('xhr', function () {
            var json = attributeValuesTable.ajax.json();
            attributeValues.total = json.recordsTotal;
        });

        $('#attributeValues_table th.dt-checkboxes-select-all').click(function () {
            allAttributeValuesClicked = true;
        });
        $('#ctrl-show-selected-attributeValues').on('change', function () {
            attributeValuesTable.ajax.reload();
        });

        var collectAttributeValues = function () {
            $('#attribute_export_form input[name="attributeValues_type"]').remove();
            $('#attribute_export_form input[name="attributeValues_data"]').remove();
            $('#attribute_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributeValues_type").val(attributeValues.type));
            $('#attribute_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributeValues_data").val(attributeValues.data));
        };
    }

    if (typeof setCustomersTable !== 'undefined') {
        function updateCustomersSelectInfo() {
            $('#customers_select_info').text(customers.type === 'selected' ? customers.data.length + ' rows selected' : customers.total - customers.data.length + ' rows selected');

        }

        var customers = {type: "unselected", data: [], total: initialData.customers.recordsTotal, filtered: initialData.customers.recordsFiltered};
        var allCustomersClicked = false;

        var customersTable = $('#customers_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.customers.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.customers.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCustomers';
                    d.extra_search_type = $('#ctrl-show-selected-customers').val();
                    d.extra_search_params = {type: customers.type, data: customers.data};
                    if (skipAjax) {
                        filterTables.customers.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.customers);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "gender"},
                {data: "firstname"},
                {data: "lastname"},
                {data: "email"},
                {data: "group"},
                {data: "enabled"},
                {data: "newsletter"},
//                {data: "deleted"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#customers_select_info').length === 0) {
                    $('#customers_table_info').append('<span id="customers_select_info" class="select_info"></span>');
                }
                updateCustomersSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (customers.type === 'unselected') {
                                    index = customers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            customers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            customers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = customers.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            customers.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            customers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCustomersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCustomersClicked) {
                                if (selected) {
                                    customers.type = 'unselected';
                                } else {
                                    customers.type = 'selected';
                                }
                                customers.data = [];
                                allCustomersClicked = false;
                            }
                            updateCustomersSelectInfo();

                            if (!indeterminate && customers.data.length !== 0 && parseInt(customers.total) !== customers.data.length) {
                                $('#customers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (customers.type === 'unselected') {
                            if (customers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (customers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 5,
                    width: "220px"
                },
                {
                    targets: 7,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                },
                {
                    targets: 8,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check catalogEI_icon"></i>' : '<i class="icon-remove catalogEI_icon"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        customersTable.on('xhr', function () {
            var json = customersTable.ajax.json();
            customers.total = json.recordsTotal;
        });

        $('#customers_table th.dt-checkboxes-select-all').click(function () {
            allCustomersClicked = true;
        });
        $('#ctrl-show-selected-customers').on('change', function () {
            customersTable.ajax.reload();
        });

        var collectCustomers = function () {
            $('#customer_export_form input[name="customers_type"]').remove();
            $('#customer_export_form input[name="customers_data"]').remove();
            $('#customer_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "customers_type").val(customers.type));
            $('#customer_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "customers_data").val(customers.data));
        };
    }

    if (typeof setAddressesTable !== 'undefined') {
        function updateAddressesSelectInfo() {
            $('#addresses_select_info').text(addresses.type === 'selected' ? addresses.data.length + ' rows selected' : addresses.total - addresses.data.length + ' rows selected');

        }

        var addresses = {type: "unselected", data: [], total: initialData.addresses.recordsTotal, filtered: initialData.addresses.recordsFiltered};
        var allAddressesClicked = false;

        var addressesTable = $('#addresses_table').DataTable({
            order: [[1, 'asc']],
            data: initialData.addresses.data,
            autoWidth: false,
            processing: true,
            serverSide: true,
            deferLoading: initialData.addresses.recordsTotal,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: controller_link,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAddresses';
                    d.extra_search_type = $('#ctrl-show-selected-addresses').val();
                    d.extra_search_params = {type: addresses.type, data: addresses.data};
                    if (skipAjax) {
                        filterTables.addresses.draw = d.draw;
                    }
                },
                beforeSend: function (jqXHR, settings) {
                    if (skipAjax) {
                        this.success(filterTables.addresses);

                        return false;
                    }
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "address1"},
                {data: "country_name"},
                {data: "city"},
                {data: "firstname"},
                {data: "lastname"},
                {data: "email"},
                {data: "brand_name"},
                {data: "supplier_name"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#addresses_select_info').length === 0) {
                    $('#addresses_table_info').append('<span id="addresses_select_info" class="select_info"></span>');
                }
                updateAddressesSelectInfo();
            },
            initComplete: function (settings, json) {
                var api = this.api();
                api.cells(api.rows(function (idx, data, node) {
                    return true;
                }).indexes(), 0).checkboxes.select();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            if (Array.isArray(nodes.selector.rows)) {
                                var index;

                                if (addresses.type === 'unselected') {
                                    index = addresses.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);

                                    if (selected) {
                                        if (index > -1) {
                                            addresses.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            addresses.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    }
                                } else {
                                    index = addresses.data.indexOf(nodes.row(nodes.selector.rows[0]).data().id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            addresses.data.push(nodes.row(nodes.selector.rows[0]).data().id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            addresses.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAddressesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAddressesClicked) {
                                if (selected) {
                                    addresses.type = 'unselected';
                                } else {
                                    addresses.type = 'selected';
                                }
                                addresses.data = [];
                                allAddressesClicked = false;
                            }
                            updateAddressesSelectInfo();

                            if (!indeterminate && addresses.data.length !== 0 && parseInt(addresses.total) !== addresses.data.length) {
                                $('#addresses_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    createdCell: function (td, cellData, rowData, row, col) {
                        if (rowData.initial) {
                            return;
                        }
                        if (addresses.type === 'unselected') {
                            if (addresses.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (addresses.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        addressesTable.on('xhr', function () {
            var json = addressesTable.ajax.json();
            addresses.total = json.recordsTotal;
        });

        $('#addresses_table th.dt-checkboxes-select-all').click(function () {
            allAddressesClicked = true;
        });
        $('#ctrl-show-selected-addresses').on('change', function () {
            addressesTable.ajax.reload();
        });

        var collectAddresses = function () {
            $('#address_export_form input[name="addresses_type"]').remove();
            $('#address_export_form input[name="addresses_data"]').remove();
            $('#address_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "addresses_type").val(addresses.type));
            $('#address_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "addresses_data").val(addresses.data));
        };
    }


    $('#scheduleEmails_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();

        var id = data.id;

        var data = {
            address: data.address,
            timestamp: data.timestamp,
            template: data.template,
            entities: data.entities,
            active: 0
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleEmail', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    scheduleEmailsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleEmails_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();

        var id = data.id;

        var data = {
            address: data.address,
            timestamp: data.timestamp,
            template: data.template,
            entities: data.entities,
            active: 1
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleEmail', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    scheduleEmailsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleFTPs_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();

        var id = data.id, url_parts = data.url.split(':');

        var data = {
            type: data.type,
            url: url_parts[0],
            port: url_parts[1] ? url_parts[1] : '',
            username: data.username,
            password: data.password,
            timestamp: data.timestamp,
            template: data.template,
            active: 0
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    scheduleFTPsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleFTPs_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();

        var id = data.id, url_parts = data.url.split(':');

        var data = {
            type: data.type,
            url: url_parts[0],
            port: url_parts[1] ? url_parts[1] : '',
            username: data.username,
            password: data.password,
            timestamp: data.timestamp,
            template: data.template,
            active: 1
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    scheduleFTPsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });


    var scheduleEmailsTable = $('#scheduleEmails_table').DataTable({
        order: [[1, 'asc']],
        data: initialData.emails.data,
        autoWidth: false,
        processing: true,
        serverSide: true,
        deferLoading: initialData.emails.recordsTotal,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getScheduleEmails';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "id"},
            {data: "address"},
            {data: "timestamp"},
            {data: "template_name"},
            {data: "active"},
            {data: ""},
            {data: "template"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "40px"
            },
            {
                targets: 3,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>';
                }
            },
            {
                targets: 5,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check change"></i>' : '<i class="icon-remove change"></i>';
                }
            },
            {
                targets: 6,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_schedule"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#d90000" class="icon-trash delete_schedule"></i>';
                }
            },
            {
                targets: 7,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#schedule_email_save').click(function () {
        var email = $('#schedule_email_address').val();
        var data = {
            address: email,
            timestamp: $('input[name="schedule_email_add_ts"]:checked').val(),
            template: $('#schedule_email_template').val(),
            active: $('input[name="schedule_email_active"]:checked').val(),
            entities: ''
        };

        $('#schedule_email_entities .prestashop-switch').each(function (index, element) {
            var elem = $(element).find('input[name^="schedule_email_"]:checked');
            if (elem.val() === '1') {
                data.entities += elem.attr('name').split('schedule_email_')[1] + ',';
            }
        });
        data.entities = data.entities.slice(0, -1);

        var id = $('#schedule_email_id').val();
        if (!email) {
            return alert(fill_required_fields);
        }
        $('#schedule_email_modal').modal('hide');
        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleEmail', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    hideLoading();
                    scheduleEmailsTable.draw(false);
                    var json = JSON.parse(result);

                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#schedule_add_new_email').click(function () {
        $('#schedule_email_modal h4.modal-title').text(add_email);
        $('#schedule_email_id').val('');
        $('#schedule_email_address').val('');
        $('#schedule_email_template').val('catalog_default');

        $('input[id^="schedule_email_"][id$="_yes"]').prop("checked", true);
        $('input[id^="schedule_email_"][id$="_no"]').prop("checked", false);
    });

    $('#scheduleEmails_table').on('click', 'td > .edit_schedule', function (e) {
        e.preventDefault();
        $('#schedule_email_modal h4.modal-title').text(edit_email);
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();
        $('#schedule_email_id').val(data.id);
        $('#schedule_email_address').val(data.address);
        $('#schedule_email_template').val(data.template);

        $('input[id^="schedule_email_"][id$="_yes"]').prop("checked", false);
        $('input[id^="schedule_email_"][id$="_no"]').prop("checked", true);

        for (var entity of data.entities.split(',')) {
            $('#schedule_email_' + entity + '_yes').prop("checked", true);
            $('#schedule_email_' + entity + '_no').prop("checked", false);
        }

        $('#schedule_email_active_yes').prop("checked", parseInt(data.active));
        $('#schedule_email_active_no').prop("checked", !parseInt(data.active));
        $('#schedule_email_add_ts_yes').prop("checked", parseInt(data.timestamp));
        $('#schedule_email_add_ts_no').prop("checked", !parseInt(data.timestamp));

        $('#schedule_email_modal').modal('show');
    });

    $('#scheduleEmails_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        var id = scheduleEmailsTable.row($(this).closest('tr')).data().id;
        if (confirm(sure_to_delete + ' "' + id + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteScheduleEmail', type: 'dml', ieType: 'export', id: id},
                    function (result) {
                        hideLoading();
                        scheduleEmailsTable.draw();
                        var json = JSON.parse(result);

                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });


    var scheduleFTPsTable = $('#scheduleFTPs_table').DataTable({
        order: [[1, 'asc']],
        data: initialData.ftps.data,
        autoWidth: false,
        processing: true,
        serverSide: true,
        deferLoading: initialData.ftps.recordsTotal,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getScheduleFTPs';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "id"},
//            {data: "type"},
            {data: "url"},
            {data: "username"},
            {data: "password"},
            {data: "timestamp"},
            {data: "template_name"},
            {data: "active"},
            {data: ""},
            {data: "template"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "30px"
            },
//            {
//                targets: 2,
//                width: "70px",
//                render: function (data, type, row, meta) {
//                    return data.toUpperCase();
//                }
//            },
            {
                targets: 5,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>';
                }
            },
            {
                targets: 6,
                width: "12%"
            },
            {
                targets: 7,
                className: "dt-center",
                width: "60px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check change"></i>' : '<i class="icon-remove change"></i>';
                }
            },
            {
                targets: 8,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_schedule"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#d90000" class="icon-trash delete_schedule"></i>';
                }
            },
            {
                targets: 9,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#schedule_ftp_save').click(function () {
        var url = $('#schedule_ftp_url').val();
        var username = $('#schedule_ftp_username').val();
        var data = {
            type: $('#schedule_ftp_type').val(),
            url: url,
            port: $('#schedule_ftp_port').val(),
            username: username,
            password: $('#schedule_ftp_password').val(),
            timestamp: $('input[name="schedule_ftp_add_ts"]:checked').val(),
            template: $('#schedule_ftp_template').val(),
            active: $('input[name="schedule_ftp_active"]:checked').val(),
            entities: ''
        };

        $('#schedule_ftp_entities .prestashop-switch').each(function (index, element) {
            var elem = $(element).find('input[name^="schedule_ftp_"]:checked');
            var name = elem.attr('name').split('schedule_ftp_')[1];
            data.entities += name + ':' + elem.val() + ':' + $('#schedule_ftp_folder_' + name).val() + ',';
        });
        data.entities = data.entities.slice(0, -1);

        var id = $('#schedule_ftp_id').val();
        if (!url || !username) {
            return alert(fill_required_fields);
        }
        $('#schedule_ftp_modal').modal('hide');
        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'export', id: id, data},
                function (result) {
                    scheduleFTPsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#schedule_add_new_ftp').click(function () {
        $('#schedule_ftp_modal h4.modal-title').text(add_ftp);
        $('#schedule_ftp_type').val('ftp');
        $('#schedule_ftp_type').change();
        $('#schedule_ftp_id').val('');
        $('#schedule_ftp_url').val('');
        $('#schedule_ftp_port').val('');
        $('#schedule_ftp_username').val('');
        $('#schedule_ftp_password').val('');
        $('#schedule_ftp_template').val('catalog_default');

        $('input[id^="schedule_ftp_"][id$="_yes"]').prop("checked", true);
        $('input[id^="schedule_ftp_"][id$="_no"]').prop("checked", false);
        $('input[id^="schedule_ftp_folder_"]').val('');
    });

    $('#scheduleFTPs_table').on('click', 'td > .edit_schedule', function (e) {
        e.preventDefault();
        $('#schedule_ftp_modal h4.modal-title').text(edit_ftp);
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();
        $('#schedule_ftp_id').val(data.id);
        $('#schedule_ftp_type').val(data.type);
        $('#schedule_ftp_type').change();
        var url = data.url.split(':');
        $('#schedule_ftp_url').val(url[0]);
        if (typeof url[1] !== 'undefined') {
            $('#schedule_ftp_port').val(url[1]);
        } else {
            $('#schedule_ftp_port').val('');
        }
        $('#schedule_ftp_username').val(data.username);
        $('#schedule_ftp_password').val(data.password);
        $('#schedule_ftp_template').val(data.template);

        for (var entity of data.entities.split(',')) {
            var vals = entity.split(':');
            if (vals.length > 3) {
                for (var i = 3; i < vals.length; i++) {
                    vals[2] += vals[i];
                    delete vals[i];
                }
            }
            $('#schedule_ftp_' + vals[0] + '_yes').prop("checked", parseInt(vals[1]));
            $('#schedule_ftp_' + vals[0] + '_no').prop("checked", !parseInt(vals[1]));

            $('#schedule_ftp_folder_' + vals[0]).val(vals[2]);
        }

        $('#schedule_ftp_add_ts_no').prop("checked", !parseInt(data.timestamp));
        $('#schedule_ftp_add_ts_yes').prop("checked", parseInt(data.timestamp));
        $('#schedule_ftp_active_no').prop("checked", !parseInt(data.active));
        $('#schedule_ftp_active_yes').prop("checked", parseInt(data.active));

        $('#schedule_ftp_modal').modal('show');
    });

    $('#scheduleFTPs_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        var id = scheduleFTPsTable.row($(this).closest('tr')).data().id;
        if (confirm(sure_to_delete + ' "' + id + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteScheduleFTP', type: 'dml', ieType: 'export', id: id},
                    function (result) {
                        scheduleFTPsTable.draw();
                        hideLoading();
                        var json = JSON.parse(result);
                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });

});
