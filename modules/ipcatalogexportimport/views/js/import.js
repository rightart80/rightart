/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

var importCancelRequest = false;
var importContinueRequest = false;
//var nbTable, currentViewTable = 0;
var mandatoryFields = {};
var entity;

$(document).ready(function () {
    // console.log('ipcatalog import.js loaded, entity =', entity);

    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        showErrorMessage(message);
    };

    function loadTemplates(templates) {
        var html = '', html2 = '';
        $.each(templates, function (index, value) {
            if (value.name === 'catalog_default') {
                html += '<li data-id="' + value.id_ipcatalogimport
                        + '" data-config=\'' + value.configuration
                        + '\' class="list-group-item"><b>' + default_template + '</b><span class="pull-right apply_span default_config">'
                    + '<button type="button" class="btn btn-default apply_config"><i class="icon-check"></i> ' + apply + '</button> | '
                    + '<a class="btn btn-default" href="' + controller_link + '&action=getSetting&st_id=' + value.id_ipcatalogimport + '"><i class="icon-download"></i></a></span></li>';
                html2 += '<option selected value="' + value.name + '">' + default_template + '</option>';
            } else {
                html += '<li data-id="' + value.id_ipcatalogimport
                        + '" data-config=\'' + value.configuration
                        + '\' class="list-group-item"><b>' + value.name + '</b><span class="pull-right apply_span">'
                    + '<button class="btn btn-default apply_config"><i class="icon-check"></i> ' + apply + '</button> | '
                    + '<a class="btn btn-default" href="' + controller_link + '&action=getSetting&st_id=' + value.id_ipcatalogimport + '"><i class="icon-download"></i></a> | '
                    + '<button type="button" class="btn btn-default delete_file"><i title="' + value.title
                    + '" class="icon-trash" style="color:#c50000" aria-hidden="true"></i></button></span></li>';
                html2 += '<option value="' + value.name + '">' + value.name + '</option>';
            }
        });
        $('#configs').html(html);
        $('#schedule_url_template').html(html2);
        $('#schedule_ftp_template').html(html2);
    }

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

    function ajaxUpload(data) {
        showLoading();
        data.append('ajax', '1');
        $.ajax({
            url: controller_link + '&action=Upload',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoading();
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    $('#after_upload_danger_alert_' + entity + ' span').text(e).closest('.after_upload_danger_alert').removeClass('hide');
                    $('#import_form_' + entity + ' input:hidden[name=filename]').val('');
                    $('#import_form_' + entity + ' input:hidden[name=file]').val('');
                    prepareForImport(entity, false);
                }
                if (response.error == 0) {
                    showUploadSuccessAlert(entity, response.filename);
                    $('#import_source_' + entity).addClass('hide');
                    $('#import_type_' + entity).addClass('hide');
                    if (data.use) {
//                        $('#upload_history_' + entity).addClass('hide');
                    } else {
//                        $('#file_uploader_' + entity).addClass('hide');
                        eval('table_' + entity).ajax.reload();
                    }

                    $('#view_data_table_' + entity).html(response.data.join(''));
                    showTable(0, response.nb_table, entity);
                    paginator[entity].nbTable = response.nb_table;
                    mandatoryFields[entity] = response.required_fields;
                    prepareForImport(entity, true);
                    $('.' + entity + ' .dynamic_buttons').html(response.pages);
                } else if (response.error != -1) {
                    $('#after_upload_danger_alert_' + entity + ' span').text(response.error).closest('.after_upload_danger_alert').removeClass('hide');
                    $('#import_form_' + entity + ' input:hidden[name=filename]').val('');
                    $('#import_form_' + entity + ' input:hidden[name=file]').val('');
                    prepareForImport(entity, false);
                } else {
                    prepareForImport(entity, false);
                }
            },
            error: function (response) {
                hideLoading();
                $('#after_upload_danger_alert_' + entity + ' span').text(response.responseText).closest('.after_upload_danger_alert').removeClass('hide');
                $('#import_form_' + entity + ' input:hidden[name=filename]').val('');
                $('#import_form_' + entity + ' input:hidden[name=file]').val('');
                prepareForImport(entity, false);
            }
        });
    }

    // Upload event
    $('.entity_file-selectbutton, .entity_file-name').click(function (e) {
        $(this).closest('.catalogEI_form').find('.entity_file').trigger('click');
    });

    $('.entity_file-name').on('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('.entity_file-name').on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('.entity_file-name').on('drop', function (e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        $(this).closest('.catalogEI_form').find('.entity_file')[0].files = files;
        $(this).val(files[0].name);
    });

    $('.entity_file').change(function (e) {
        entity = $(this).data('entity');
        var files = $(this)[0].files;
        if (files !== undefined) {
            var name = '';
            $.each(files, function (index, value) {
                name += value.name + ', ';
            });
            $(this).closest('.catalogEI_form').find('.entity_file-name').val(name.slice(0, -2));
        } else { // Internet Explorer 9 Compatibility
            var name = $(this).val().split(/[\\/]/);
            $(this).closest('.catalogEI_form').find('.entity_file-name').val(name[name.length - 1]);
        }

        // Upload file to server with AJAX
        ajaxUpload(new FormData(document.querySelector('#import_form_' + entity)));
    });

    if (typeof entity_file_max_files !== 'undefined')
    {
        $('.entity_file').closest('form').on('submit', function (e) {
            if ($(this).find('.entity_file')[0].files.length > entity_file_max_files) {
                e.preventDefault();
                alert('You can upload a maximum of  files');
            }
        });
    }
    // END Upload event

    // Enable collapse upon initialization
    $(".collapse").collapse({toggle: false});

    $('select[name="import_source"]').change(function () {
        if ($(this).val() === 'file_upload') {
            $(this).closest('form.catalogEI_form').find('.import_type').addClass("hide");
            $(this).closest('form.catalogEI_form').find('.file_upload').removeClass("hide");
        } else if ($(this).val() === 'from_url') {
            $(this).closest('form.catalogEI_form').find('.import_type').addClass("hide");
            $(this).closest('form.catalogEI_form').find('.from_url').removeClass("hide");
        } else if ($(this).val() === 'from_ftp') {
            $(this).closest('form.catalogEI_form').find('.import_type').addClass("hide");
            $(this).closest('form.catalogEI_form').find('.from_ftp').removeClass("hide");
        } else if ($(this).val() === 'from_history') {
            $(this).closest('form.catalogEI_form').find('.import_type').addClass("hide");
            $(this).closest('form.catalogEI_form').find('.upload_history').removeClass("hide");
        }
    });

    $('select[name="target_action"]').change(function () {
        if (this.value === 'insert') {
            $(this).closest('.catalogEI_configurations').find('div.update_options').addClass("hide");
            $(this).closest('.catalogEI_configurations').find('div.insert_options').removeClass("hide");
        } else if (this.value === 'update') {
            $(this).closest('.catalogEI_configurations').find('div.insert_options').addClass("hide");
            $(this).closest('.catalogEI_configurations').find('div.update_options').removeClass("hide");
        } else if (this.value === 'update_insert') {
            $(this).closest('.catalogEI_configurations').find('div.insert_options, div.update_options').removeClass("hide");
        }
    });

    $('.catalogEI_form .file_download_from_url_btn').click(function (e) {
        e.preventDefault();

        entity = $(this).data('entity');
        var fd = new FormData(document.querySelector('#import_form_' + entity));
        var url = $('#file_url_' + entity).val();
        fd.append('from_url', url);

        // Upload file to server with AJAX
        ajaxUpload(fd);
    });

    $('.catalogEI_form .file_download_from_ftp_btn').click(function (e) {
        e.preventDefault();

        entity = $(this).data('entity');
        var fd = new FormData(document.querySelector('#import_form_' + entity));
        fd.append('from_ftp', 1);
        fd.append('ftp_type', $('#ftp_type_' + entity).val());
        fd.append('ftp_url', $('#ftp_url_' + entity).val());
        fd.append('ftp_port', $('#ftp_port_' + entity).val());
        fd.append('ftp_username', $('#ftp_username_' + entity).val());
        fd.append('ftp_password', $('#ftp_password_' + entity).val());
        fd.append('ftp_filepath', $('#ftp_filepath_' + entity).val());

        // Upload file to server with AJAX
        ajaxUpload(fd);
    });

    // Sortable
    $('.scrollable ul').sortable({
        cursor: "move",
        axis: "y",
        stop: function (event, ui) {
            ui.item.css('left', '');
        }
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

    // If the "x" icon is clicked in the columns search input
    $('.clearable_clear').click(function () {
        $(this).prev('.fields_search').val('').trigger('keyup');
    });

    // When clicking the "Next" and "Previous" buttons on a demonstration table data
    $('.catalogEI_view_data button.go_left').click(function (e) {
        var entity = $(this).data('entity');
        showTable(paginator[entity].currentViewTable - 1, paginator[entity].nbTable, entity);
    });
    $('.catalogEI_view_data button.go_right').click(function (e) {
        var entity = $(this).data('entity');
        showTable(paginator[entity].currentViewTable + 1, paginator[entity].nbTable, entity);
    });
    $(document).on('click', '.btn_page', function (e) {
        var entity = $(this).data('entity');
        showTable($(this).text() - 1, paginator[entity].nbTable, entity);
    });
    $(document).on('focus', '.paginate button', function (e) {
        $(this).blur();
    });

    // When clicking the "Next" button
    $('.panel-footer button.next').click(function (e) {
        e.preventDefault();
        $('#catalogEI-' + $(this).data('value')).find('.nav.nav-tabs a:last').tab('show');
    });

    $('.panel-footer button.back').click(function (e) {
        e.preventDefault();
        $('#catalogEI-' + $(this).data('value')).find('.nav.nav-tabs a:first').tab('show');
    });

    // "Choose from history" button and "Close" clicked
    $('.show_upload_history, .close_history_btn').click(function (e) {
        $(this).closest('.catalogEI_configurations').find('.file_uploader').toggleClass('hide');
    });

    // "Use" button clicked
    $('.upload_history_table').on('click', 'td > .use_file_btn', function (e) {
        entity = $(this).closest('.upload_history_table').data('table');
        var data = eval('table_' + entity).row($(this).closest('tr')).data();
        var fd = new FormData(document.querySelector('#import_form_' + entity));
        fd.append('use', data.file);

        // Upload file to server with AJAX
        ajaxUpload(fd);
    });

    // "Delete file" icon clicked
    $('.upload_history_table').on('click', 'td > .delete_file', function (e) {
        entity = $(this).closest('.upload_history_table').data('table');
        var data = eval('table_' + entity).row($(this).closest('tr')).data();

        showLoading();
        $.post(controller_link, {entity: entity, deleteFile: data.file},
                function (result) {
                    hideLoading();
                    eval('table_' + entity).ajax.reload();
                    var json = JSON.parse(result);
                    if (json.state === 1) {
                        showSuccessMessage(json.msg);
                    } else {
                        showErrorMessage(json.msg);
                    }
                });
    });

    // "Change" button clicked
    $('.change_file_btn').click(function (e) {
        $(this).closest('.catalogEI_configurations').find('.after_upload_success_alert').addClass('hide');
        $(this).closest('.catalogEI_configurations').find('.file_uploader').removeClass('hide');
        var entity = $(this).closest('form.catalogEI_form').find('input[name="entity"]').val();
        $('#import_source_' + entity).removeClass('hide');
        $('#import_type_' + entity).removeClass('hide');
    });

    // Save changes button is clicked
    $('#save_template_btn').click(function (e) {
        e.preventDefault();
        var name = $('#template_name').val();
        if (!name) {
            return alert(invalid_template_name);
        }

        var config = {};
        $('.catalogEI form.catalogEI_form').each(function (index, element) {
            config[$(element).find('input[name=entity]').val()] = decodeURIComponent($(element).serialize()).replace(/\+/g, ' ');
        });

        showLoading();
        $.post(controller_link, {ajax_action: 'saveConfig', type: 'dml', ieType: 'import', name: name, config: JSON.stringify(config)},
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
        showLoading();
        $.each(JSON.parse($(this).parent().parent().attr('data-config')), function (name, value) {
            var pairs = value.split('&');
            var pair;
            for (pair of pairs) {
                pair = pair.split('=');
                if ($('[name="' + pair[0] + '"]').attr('type') === 'radio') {
                    $('#import_form_' + name).find('input[name="' + pair[0] + '"][value="' + pair[1] + '"]').prop('checked', true);
                } else if (pair[0] !== 'filename') {
                    $('#import_form_' + name).find('[name="' + pair[0] + '"]').val(pair[1]);
                }
            }
        });
//        $('select[name="import_source"]').change();
        hideLoading();
        showSuccessMessage(template_loaded);

    });

    // Click trash
    $(document).on('click', '#configs button.delete_file', function (e) {
        if (confirm(sure_to_delete + ' "' + $(this).parent().parent().find('b').text() + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteConfig', type: 'dml', ieType: 'import', id: $(this).parent().parent().attr('data-id')},
                    function (result) {
                        var json = JSON.parse(result);
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
                hideLoading();
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
            updateSchedule('IPI_SCHDL_ENABLE', $('input[name="schedule"]:checked').val());
        }
    });
    $('#schedule_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule').collapse("hide");
            updateSchedule('IPI_SCHDL_ENABLE', $('input[name="schedule"]:checked').val());
        }
    });

    $('#schedule_use_url_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-url').collapse("show");
            updateSchedule('IPI_SCHDL_USE_URL', $('input[name="schedule_use_url"]:checked').val());
        }
    });
    $('#schedule_use_url_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-url').collapse("hide");
            updateSchedule('IPI_SCHDL_USE_URL', $('input[name="schedule_use_url"]:checked').val());
        }
    });

    $('#schedule_use_ftp_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("show");
            updateSchedule('IPI_SCHDL_USE_FTP', $('input[name="schedule_use_ftp"]:checked').val());
        }
    });
    $('#schedule_use_ftp_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("hide");
            updateSchedule('IPI_SCHDL_USE_FTP', $('input[name="schedule_use_ftp"]:checked').val());
        }
    });


    $('#scheduleURLs_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = scheduleURLsTable.row($(this).closest('tr')).data();

        var id = data.id;

        var data = {
            url: data.url,
            template: data.template,
            entity: data.entity,
            active: 0
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleURL', type: 'dml', ieType: 'import', id: id, data},
                function (result) {
                    scheduleURLsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleURLs_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = scheduleURLsTable.row($(this).closest('tr')).data();

        var id = data.id;

        var data = {
            url: data.url,
            template: data.template,
            entity: data.entity,
            active: 1
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleURL', type: 'dml', ieType: 'import', id: id, data},
                function (result) {
                    scheduleURLsTable.draw(false);
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
            entities: data.entities,
            active: 0
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'import', id: id, data},
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
            entities: data.entities,
            active: 1
        };

        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'import', id: id, data},
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

    var scheduleURLsTable = $('#scheduleURLs_table').DataTable({
        order: [[1, 'asc']],
        data: initialData.urls.data,
        autoWidth: false,
        processing: true,
        serverSide: true,
        deferLoading: initialData.urls.recordsTotal,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getImportScheduleURLs';
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
            {data: "translated_entity"},
            {data: "url"},
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
            {
                targets: 2,
                width: "140px"
            },
            {
                targets: 4,
                width: "12%"
            },
            {
                targets: 5,
                className: "dt-center",
                width: "60px",
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

    $('#schedule_url_save').click(function () {
        var url = $('#schedule_url_url').val();
        var entity = $('#schedule_url_entity').val();
        var data = {
            url: url,
            entity: entity,
            template: $('#schedule_url_template').val(),
            active: $('input[name="schedule_url_active"]:checked').val(),
        };

        var id = $('#schedule_url_id').val();
        if (!url || !entity) {
            return alert(fill_required_fields);
        }
        $('#schedule_url_modal').modal('hide');
        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleURL', type: 'dml', ieType: 'import', id: id, data},
                function (result) {
                    scheduleURLsTable.draw(false);
                    hideLoading();
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#schedule_add_new_url').click(function () {
        $('#schedule_url_modal h4.modal-title').text(add_url);
        $('#schedule_url_id').val('');
        $('#schedule_url_url').val('');
        $('#schedule_url_entity').val('products');
        $('#schedule_url_template').val('catalog_default');

        $('input[id^="schedule_url_"][id$="_yes"]').prop("checked", true);
        $('input[id^="schedule_url_"][id$="_no"]').prop("checked", false);
    });

    $('#scheduleURLs_table').on('click', 'td > .edit_schedule', function (e) {
        e.preventDefault();
        $('#schedule_url_modal h4.modal-title').text(edit_url);
        var data = scheduleURLsTable.row($(this).closest('tr')).data();
        $('#schedule_url_id').val(data.id);
        $('#schedule_url_url').val(data.url);
        $('#schedule_url_entity').val(data.entity);
        $('#schedule_url_template').val(data.template);

        $('#schedule_url_active_no').prop("checked", !parseInt(data.active));
        $('#schedule_url_active_yes').prop("checked", parseInt(data.active));

        $('#schedule_url_modal').modal('show');
    });

    $('#scheduleURLs_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        var id = scheduleURLsTable.row($(this).closest('tr')).data().id;
        if (confirm(sure_to_delete + ' "' + id + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteScheduleURL', type: 'dml', ieType: 'import', id: id},
                    function (result) {
                        scheduleURLsTable.draw();
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
                d.ajax_action = 'getImportScheduleFTPs';
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
                width: "12%"
            },
            {
                targets: 6,
                className: "dt-center",
                width: "60px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check change"></i>' : '<i class="icon-remove change"></i>';
                }
            },
            {
                targets: 7,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_schedule"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#d90000" class="icon-trash delete_schedule"></i>';
                }
            },
            {
                targets: 8,
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
            template: $('#schedule_ftp_template').val(),
            active: $('input[name="schedule_ftp_active"]:checked').val(),
            entities: ''
        };

        for (var i = 0; i < $('#schedule_ftp_entities .with-position').length; i++) {
            var elem = $('#schedule_ftp_entities .with-position[data-position=' + i + '] input[name^="schedule_ftp_"]:checked');
            var name = elem.attr('name').split('schedule_ftp_')[1];
            data.entities += name + ':' + elem.val() + ':' + $('#schedule_ftp_folder_' + name).val() + ',';
        }
        data.entities = data.entities.slice(0, -1);

        var id = $('#schedule_ftp_id').val();
        if (!url || !username) {
            return alert(fill_required_fields);
        }
        $('#schedule_ftp_modal').modal('hide');
        showLoading();
        $.post(controller_link, {ajax_action: 'saveScheduleFTP', type: 'dml', ieType: 'import', id: id, data},
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

        $('#schedule_ftp_active_no').prop("checked", !parseInt(data.active));
        $('#schedule_ftp_active_yes').prop("checked", parseInt(data.active));

        $('#schedule_ftp_modal').modal('show');
    });

    $('#scheduleFTPs_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        var id = scheduleFTPsTable.row($(this).closest('tr')).data().id;
        if (confirm(sure_to_delete + ' "' + id + '" ?')) {
            showLoading();
            $.post(controller_link, {ajax_action: 'deleteScheduleFTP', type: 'dml', ieType: 'import', id: id},
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

    // When the resfresh button is clicked
    $('.refresh_button').click(function (e) {
        e.preventDefault();
        var table = $(this).attr('id').substr(8);
        eval(table + 'Table').ajax.reload();
    });

    var skipAjax = true;
    // Get import history
    var table_brands = $('#upload_history_table_brands').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'brands';
            },
            beforeSend: function (jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server
                if (skipAjax) { //if fake AJAX flag is set
                    this.success(files.brands); //call success function of current AJAX object (while passing fake data) which is used by DataTable on successful response from server

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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=brands&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_brands.on('xhr', function () {
        var json = table_brands.ajax.json();
        $('#file_uploader_brands .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_brands span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_brands button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_suppliers = $('#upload_history_table_suppliers').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'suppliers';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.suppliers);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=suppliers&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_suppliers.on('xhr', function () {
        var json = table_suppliers.ajax.json();
        $('#file_uploader_suppliers .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_suppliers span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_suppliers button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_discounts = $('#upload_history_table_discounts').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'discounts';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.discounts);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=discounts&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_discounts.on('xhr', function () {
        var json = table_discounts.ajax.json();
        $('#file_uploader_discounts .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_discounts span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_discounts button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_warehouses = $('#upload_history_table_warehouses').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'warehouses';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.warehouses);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=warehouses&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_warehouses.on('xhr', function () {
        var json = table_warehouses.ajax.json();
        $('#file_uploader_warehouses .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_warehouses span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_warehouses button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_aliases = $('#upload_history_table_aliases').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'aliases';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.aliases);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=aliases&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_aliases.on('xhr', function () {
        var json = table_aliases.ajax.json();
        $('#file_uploader_aliases .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_aliases span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_aliases button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_stores = $('#upload_history_table_stores').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'stores';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.stores);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=stores&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_stores.on('xhr', function () {
        var json = table_stores.ajax.json();
        $('#file_uploader_stores .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_stores span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_stores button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_combinations = $('#upload_history_table_combinations').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'combinations';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.combinations);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=combinations&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_combinations.on('xhr', function () {
        var json = table_combinations.ajax.json();
        $('#file_uploader_combinations .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_combinations span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_combinations button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_features = $('#upload_history_table_features').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'features';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.features);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=features&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_features.on('xhr', function () {
        var json = table_features.ajax.json();
        $('#file_uploader_features .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_features span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_features button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_attributes = $('#upload_history_table_attributes').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'attributes';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.attributes);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=attributes&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_attributes.on('xhr', function () {
        var json = table_attributes.ajax.json();
        $('#file_uploader_attributes .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_attributes span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_attributes button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_packs = $('#upload_history_table_packs').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'packs';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.packs);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=packs&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_packs.on('xhr', function () {
        var json = table_packs.ajax.json();
        $('#file_uploader_packs .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_packs span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_packs button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_categories = $('#upload_history_table_categories').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'categories';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.categories);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=categories&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_categories.on('xhr', function () {
        var json = table_categories.ajax.json();
        $('#file_uploader_categories .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_categories span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_categories button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_products = $('#upload_history_table_products').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'products';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.products);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=products&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
//                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_products.on('xhr', function () {
        var json = table_products.ajax.json();
        $('#file_uploader_products .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_products span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_products button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_carriers = $('#upload_history_table_carriers').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'carriers';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.carriers);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=carriers&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_carriers.on('xhr', function () {
        var json = table_carriers.ajax.json();
        $('#file_uploader_carriers .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_carriers span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_carriers button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_customers = $('#upload_history_table_customers').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'customers';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.customers);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=customers&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_customers.on('xhr', function () {
        var json = table_customers.ajax.json();
        $('#file_uploader_customers .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_customers span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_customers button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_groups = $('#upload_history_table_groups').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'groups';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.groups);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=groups&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_groups.on('xhr', function () {
        var json = table_groups.ajax.json();
        $('#file_uploader_groups .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_groups span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_groups button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    var table_addresses = $('#upload_history_table_addresses').DataTable({
        order: [[0, 'desc']],
        autoWidth: false,
//        processing: true,
//        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: controller_link,
            type: "GET",
            data: function (d) {
                d.ajax_action = 'getUploadedFiles';
                d.entity = 'addresses';
            },
            beforeSend: function (jqXHR, settings) {
                if (skipAjax) {
                    this.success(files.addresses);
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
            {data: "file"},
            {data: "size"},
            {data: ""}
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row, meta) {
                    return '<a href="' + controller_link + '&entity=addresses&downloadFile=' + data + '" target="_blank">' + data + '</a>';
                }
            },
            {
                targets: 2,
                className: "dt-right",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default use_file_btn"><i class="icon-check"></i> ' + use + '</button> &nbsp;&nbsp;<button type="button" class="btn btn-default delete_file"><i title="' + delet + '" class="icon-trash"></i></button>';
                }
            }
        ]
    });
    table_addresses.on('xhr', function () {
        var json = table_addresses.ajax.json();
        $('#file_uploader_addresses .show_upload_history span.badge-info').text(json.recordsTotal);
        $('#upload_history_addresses span.file_history_nb').text(json.recordsTotal);
        $('#file_uploader_addresses button.show_upload_history').prop("disabled", !parseInt(json.recordsTotal));
    });

    skipAjax = false;

    // Begin import process
    $(document).on('submit', '.catalogEI_form', function (e) {
        e.preventDefault();
        entity = $(this).find('input[name=entity]').val();
        validateImport();
    });

    // From import.js
    $('#saveImportMatchs').unbind('click').click(function () {

        var newImportMatchs = $('#newImportMatchs').val();
        if (newImportMatchs == '')
            jAlert(errorEmpty);
        else
        {
            var matchFields = '';
            $('.type_value').each(function () {
                matchFields += '&' + $(this).attr('id') + '=' + $(this).val();
            });
            $.ajax({
                type: 'POST',
                url: 'index.php',
                async: false,
                cache: false,
                dataType: "json",
                data: 'ajax=1&action=saveImportMatchs&tab=AdminImport&token=' + token + '&skip=' + $('input[name=skip]').val() + '&newImportMatchs=' + newImportMatchs + matchFields,
                success: function (jsonData)
                {
                    $('#valueImportMatchs').append('<option id="' + jsonData.id + '" value="' + matchFields + '" selected="selected">' + newImportMatchs + '</option>');
                    $('#selectDivImportMatchs').fadeIn('slow');
                },
                error: function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jAlert('TECHNICAL ERROR Details: ' + html_escape(XMLHttpRequest.responseText));
                }
            });

        }
    });

    $('#loadImportMatchs').unbind('click').click(function () {

        var idToLoad = $('select#valueImportMatchs option:selected').attr('id');
        $.ajax({
            type: 'POST',
            url: 'index.php',
            async: false,
            cache: false,
            dataType: "json",
            data: 'ajax=1&action=loadImportMatchs&tab=AdminImport&token=' + token + '&idImportMatchs=' + idToLoad,
            success: function (jsonData)
            {
                var matchs = jsonData.matchs.split('|')
                $('input[name=skip]').val(jsonData.skip);
                for (i = 0; i < matchs.length; i++)
                    $('#type_value\\[' + i + '\\]').val(matchs[i]).attr('selected', true);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
                jAlert('TECHNICAL ERROR Details: ' + html_escape(XMLHttpRequest.responseText));

            }
        });
    });

    $('#deleteImportMatchs').unbind('click').click(function () {

        var idToDelete = $('select#valueImportMatchs option:selected').attr('id');
        $.ajax({
            type: 'POST',
            url: 'index.php',
            async: false,
            cache: false,
            dataType: "json",
            data: 'ajax=1&action=deleteImportMatchs&tab=AdminImport&token=' + token + '&idImportMatchs=' + idToDelete,
            success: function (jsonData)
            {
                $('select#valueImportMatchs option[id=\'' + idToDelete + '\']').remove();
                if ($('select#valueImportMatchs option').length == 0)
                    $('#selectDivImportMatchs').fadeOut();
            },
            error: function (XMLHttpRequest, textStatus, errorThrown)
            {
                jAlert('TECHNICAL ERROR Details: ' + html_escape(XMLHttpRequest.responseText));
            }
        });

    });

    $('#import_stop_button').unbind('click').click(function () {
        if (importContinueRequest) {
            $('#importProgress').modal('hide');
            importContinueRequest = false;
            window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
        } else {
            importCancelRequest = true;
            $('#import_details_progressing').hide();
            $('#import_details_finished').hide();
            $('#import_details_stop').show();
            $('#import_stop_button').hide();
            $('#import_close_button').hide();
            if ($('#import_continue_button').is(":visible")) {
                $('#importProgress').modal('hide');
            }
        }
    });

    $('#import_continue_button').unbind('click').click(function () {
        $('#import_continue_button').hide();
        importContinueRequest = false;
        $('#import_progress_div').show();
        $('#import_details_warning ul, #import_details_info ul').html('');
        $('#import_details_warning, #import_details_info').hide();
        importNow(0, 500, -1, false, {}, 0);
    });

    $(document).on('change', 'select.type_value', function () {
        $(this).removeClass('field-select-warning');
    });

    $('.map-fields').click(function () {
        var notFoundCount = 0;
        var entity = $(this).closest('form').find('input[name="entity"]').val();
        $(this).closest('.catalogEI_fields').find('.catalogEI_view_data_table table tbody tr:first-child td').each(function (index, elem) {
            var found = false;
            $("#type_value_" + entity + "\\[" + index + "\\] > option").each(function () {
                if ($(elem).text().toLowerCase() == this.text.replace(/\s\*{1,2}/, '').toLowerCase()) {
                    $(this).parent().val(this.value);
                    found = true;
                }
            });
            if (found) {
                $("#type_value_" + entity + "\\[" + index + "\\]").removeClass('field-select-warning');
            } else {
                $("#type_value_" + entity + "\\[" + index + "\\]").addClass('field-select-warning');
                notFoundCount++;
            }
        });
        if (!notFoundCount) {
            showSuccessMessage(all_fields_mapped);
        } else if (notFoundCount == 1) {
//            showNoticeMessage(notFoundCount + ' field not found');
            $.growl.warning({title: "", message: one_field_not_found});
        } else {
//            showNoticeMessage(notFoundCount + ' fields not found');
            $.growl.warning({title: "", message: some_fields_not_found.replace('#%d', notFoundCount)});
        }
    });

    $('.ignore-fields').click(function () {
        var entity = $(this).closest('form').find('input[name="entity"]').val();
        $(this).closest('.catalogEI_fields').find('.catalogEI_view_data_table table thead tr:first-child th').each(function (index, elem) {
            $("#type_value_" + entity + "\\[" + index + "\\] > option").each(function () {
                $(this).parent().val("no");
            });

        });

        showSuccessMessage(all_fields_ignored);

    });

    $('.save-apply-fields').click(function () {
        $('#link-Save').trigger('click');

    });
});

function showUploadSuccessAlert(entity, msg) {
    $('#after_upload_success_alert_' + entity + ' span').text(msg);
    $('#after_upload_success_alert_' + entity).removeClass('hide');
    $('#import_form_' + entity + ' input:hidden[name=filename]').val(msg);
    $('#import_form_' + entity + ' input:hidden[name=file]').val('');
    $('#after_upload_danger_alert_' + entity).addClass('hide');
}

function showTable(nb, nb_table, entity) {
    $('#btn_left_' + entity + ', #btn_right_' + entity).prop("disabled", false);
    if (nb <= 0) {
        nb = 0;
        $('#btn_left_' + entity).prop("disabled", true);
    }
    if (nb >= nb_table - 1) {
        nb = nb_table - 1;
        $('#btn_right_' + entity).prop("disabled", true);
    }
    $('#table_' + entity + paginator[entity].currentViewTable).hide();
    paginator[entity].currentViewTable = nb;
    $('#table_' + entity + paginator[entity].currentViewTable).show();

    $('.paginate.' + entity + ' .btn_hideable').addClass('hide');
    if (nb < 4) {
        $('#btn_' + entity + '_left_dots').addClass('hide');
        $('#btn_' + entity + '_right_dots').removeClass('hide');
        for (let i = 1; i <= 5; i++) {
            $('#btn_' + entity + '_page_' + i).removeClass('hide');
        }
    } else if (nb >= 4 && nb < nb_table - 4) {
        $('#btn_' + entity + '_left_dots, #btn_' + entity + '_right_dots').removeClass('hide');
        $('#btn_' + entity + '_page_' + nb).removeClass('hide');
        $('#btn_' + entity + '_page_' + (nb + 1)).removeClass('hide');
        $('#btn_' + entity + '_page_' + (nb + 2)).removeClass('hide');
    } else {
        $('#btn_' + entity + '_right_dots').addClass('hide');
        $('#btn_' + entity + '_left_dots').removeClass('hide');
        for (let i = nb_table; i > nb_table - 5; i--) {
            $('#btn_' + entity + '_page_' + i).removeClass('hide');
        }
    }

    $('.paginate.' + entity + ' button.activated').removeClass('activated');
    $('#btn_' + entity + '_page_' + (nb + 1)).addClass('activated');
}

function showLoading() {
    $('#fader, #spinner').css('display', 'block');
}

function hideLoading() {
    $('#fader, #spinner').css('display', 'none');
}

function prepareForImport(entity, show) {
    if (show) {
        $(':input[type="submit"][name="import_' + entity + '"]').prop('disabled', false);
//        $('#view_data_preupload_' + entity).addClass('hide');
//        $('#view_data_' + entity).removeClass('hide');
    } else {
        $(':input[type="submit"][name="import_' + entity + '"]').prop('disabled', true);
//        $('#view_data_preupload_' + entity).removeClass('hide');
//        $('#view_data_' + entity).addClass('hide');
    }
}

function validateImport()
{
    var mandatory = mandatoryFields[entity];
//    var type_value = [];
    var seted_value = [];
    var elem;
//    var col = 'unknow';

    $('#error_duplicate_type_' + entity).hide();
    $('#required_column_' + entity).hide();
    for (i = 0; elem = document.getElementById('type_value_' + entity + '[' + i + ']'); i++) {
        if (seted_value[elem.options[elem.selectedIndex].value]) {
            scroll(0, 0);
            $('#error_duplicate_type_' + entity + ' > strong').text(elem.options[elem.selectedIndex].innerHTML);
            $('#error_duplicate_type_' + entity).show();
            return false;
        } else if (elem.options[elem.selectedIndex].value != 'no') {
            seted_value[elem.options[elem.selectedIndex].value] = true;
        }
    }
    for (needed in mandatory) {
        if (document.getElementById(entity + '_target_action').value !== 'update' && !seted_value[mandatory[needed]]) {
            scroll(0, 0);
            $('#required_column_' + entity).show();
            $('#missing_column_' + entity).html(mandatory[needed]);
            elem = document.getElementById('type_value_' + entity + '[0]');
            for (i = 0; i < elem.length; ++i) {
                if (elem.options[i].value == mandatory[needed]) {
                    $('#missing_column_' + entity).html(elem.options[i].innerHTML);
                    break;
                }
            }
            return false;
        }
    }

    importNow(0, 500, -1, true, {}, 0); // starts with 5 elements to import, but the limit will be adapted for next calls automatically.
    return false; // We return false to avoid form to be posted on the old Controller::postProcess() action
}

function importNow(offset, limit, total, validateOnly, crossStepsVariables, moreStep, moreStepLabel) {
    if (offset == 0 && validateOnly) {
        updateProgressionInit(); // first step only, in validation mode
    }
    if (offset == 0 && !validateOnly) {
        updateProgression(0, total, limit, false, moreStep, moreStepLabel);
    }

    var data = $('form#import_form_' + entity).serializeArray();
    data.push({'name': 'crossStepsVars', 'value': JSON.stringify(crossStepsVariables)});

    var startingTime = new Date().getTime();
    $.ajax({
        type: 'POST',
        url: controller_link + '&ajax=1&action=import&offset=' + offset + '&limit=' + limit + (validateOnly ? '&validateOnly=1' : '') + ((moreStep > 0) ? '&moreStep=' + moreStep : ''),
        cache: false,
        dataType: "json",
        data: data,
        success: function (jsonData)
        {
            if (!jsonData) {
                showErrorMessage('An error occured');
            }

            if (jsonData.totalCount) {
                total = jsonData.totalCount;
            }

            if (jsonData.informations && jsonData.informations.length > 0) {
                updateValidationInfo('<li>' + jsonData.informations.join('</li><li>') + '</li>');
            }
            if (jsonData.warnings && jsonData.warnings.length > 0) {
                if (validateOnly)
                    updateValidationError('<li>' + jsonData.warnings.join('</li><li>') + '</li>', true);
                else
                    updateProgressionError('<li>' + jsonData.warnings.join('</li><li>') + '</li>', true);
            }
            if (jsonData.errors && jsonData.errors.length > 0) {
                if (validateOnly)
                    updateValidationError('<li>' + jsonData.errors.join('</li><li>') + '</li>', false);
                else
                    updateProgressionError('<li>' + jsonData.errors.join('</li><li>') + '</li>', false);
                return; // If errors, stops process
            }

            // Here, no errors returned
            if (!jsonData.isFinished == true) {
                // compute time taken by previous call to adapt amount of elements by call.
                var previousDelay = new Date().getTime() - startingTime;
                var targetDelay = 5000; // try to keep around 5 seconds by call
                // acceleration will be limited to 4 to avoid newLimit to increase too fast (NEVER reach 30 seconds by call!).
                var acceleration = Math.min(4, (targetDelay / previousDelay));
                // keep between 5 to 100 elements to process in one call
                // var newLimit = Math.min(100, Math.max(5, Math.floor(limit * acceleration)));
               var newLimit = limit;   // or even: var newLimit = 50;

                var newOffset = offset + limit;
                // update progression
                if (validateOnly) {
                    updateValidation(jsonData.doneCount, total, jsonData.doneCount + newLimit);
                } else {
                    updateProgression(jsonData.doneCount, total, jsonData.doneCount + newLimit, false, moreStep, jsonData.moreStepLabel);
                }

                if (importCancelRequest == true) {
                    $('#importProgress').modal('hide');
                    importCancelRequest = false;
//                    window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
                    return; // stops execution
                }

                // process next group of elements
                importNow(newOffset, newLimit, total, validateOnly, jsonData.crossStepsVariables, moreStep);

                // checks if we could go over post_max_size setting. Warns when reach 90% of the actual setting
                if (jsonData.nextPostSize >= jsonData.postSizeLimit * 0.9) {
                    var progressionDone = jsonData.doneCount * 100 / total;
                    var increase = Math.max(7, parseInt(jsonData.postSizeLimit / (progressionDone * 1024 * 1024))) + 1; // min 8MB
                    $('#import_details_post_limit_value').html(increase + " MB");
                    $('#import_details_post_limit').show();
                }

            } else {
                if (validateOnly) {
                    // update validation bar and process real import
                    updateValidation(total, total, total);
                    if (!$('#import_details_warning').is(":visible")) {
                        // no warning, directly import now
                        $('#import_progress_div').show();
                        importNow(0, 500, total, false, {}, 0);
                    } else {
                        // warnings occured. Ask if should continue to true import now
                        importContinueRequest = false; //true;
                        $('#import_continue_button').show();
                    }
                } else {
                    if (jsonData.oneMoreStep > moreStep) {
                        updateProgression(total, total, total, false, 0, null); // do not close now
                        importNow(0, 500, total, false, jsonData.crossStepsVariables, jsonData.oneMoreStep, jsonData.moreStepLabel);
                    } else {
                        updateProgression(total, total, total, true, moreStep, jsonData.moreStepLabel);
                    }

                }
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown)
        {
//            if (textStatus == 'parsererror') {
//                textStatus = 'Technical error: Unexpected response returned by server. Import stopped.';
            textStatus = XMLHttpRequest.responseText;
//            }
            if (validateOnly) {
                updateValidationError(textStatus, false);
            } else {
                updateProgressionError(textStatus, false);
            }
        }
    });
}

function updateProgressionInit() {
    $('#importProgress').modal({backdrop: 'static', keyboard: false, closable: false});
    $('#importProgress').modal('show');
    $('#importProgress').on('hidden.bs.modal', function () {
//        window.location.href = window.location.href.split('#')[0]; // reload same URL but do not POST again (so in GET without param)
    });

    $('#import_details_progressing').show();
    $('#import_details_finished').hide();
    $('#import_details_error').hide();
    $('#import_details_warning, #import_details_info').hide();
    $('#import_details_stop').hide();
    $('#import_details_post_limit').hide();
    $('#import_details_error ul').html('');
    $('#import_details_warning ul, #import_details_info ul').html('');

    $('#import_validation_details').html($('#import_validation_details').attr('default-value'));
    $('#validate_progressbar_done').width('0%');
    $('#validate_progressbar_done').parent().addClass('active progress-striped');
    $('#validate_progression_done').html('0');
    $('#validate_progressbar_done2').width('0%');
    $('#validate_progressbar_next').width('0%');
    $('#validate_progressbar_next').removeClass('progress-bar-danger');
    $('#validate_progressbar_next').addClass('progress-bar-info');

    $('#import_progress_div').hide();
    $('#import_progression_details').html($('#import_progression_details').attr('default-value'));
    $('#import_progressbar_done').width('0%');
    $('#import_progressbar_done').parent().addClass('active progress-striped');
    $('#import_progression_done').html('0');
    $('#import_progressbar_next').width('0%');
    $('#import_progressbar_next').removeClass('progress-bar-danger');
    $('#import_progressbar_next').addClass('progress-bar-success');

    $('#import_stop_button').show();
    $('#import_close_button').hide();
    $('#import_continue_button').hide();
}

function updateValidation(currentPosition, total, nextPosition) {
    if (currentPosition > total)
        currentPosition = total;
    if (nextPosition > total)
        nextPosition = total;

    var progressionDone = currentPosition * 100 / total;
    var progressionNext = nextPosition * 100 / total;

    if (total > 0) {
        $('#import_validate_div').show();
        $('#import_validation_details').html(currentPosition + '/' + total);
        $('#validate_progressbar_done').width(progressionDone + '%');
        $('#validate_progression_done').html(parseInt(progressionDone));
        $('#validate_progressbar_next').width((progressionNext - progressionDone) + '%');
    }

    if (currentPosition == total && total == nextPosition) {
        $('#validate_progressbar_done').parent().removeClass('active progress-striped');
    }
}

function updateProgression(currentPosition, total, nextPosition, finish, moreStep, moreStepLabel) {
    if (currentPosition > total) {
        currentPosition = total;
    }
    if (nextPosition > total) {
        nextPosition = total;
    }

    var progressionDone = currentPosition * 100 / total;
    var progressionNext = nextPosition * 100 / total;

    if (moreStep == 0 && currentPosition == 0) {
        $('#import_progressbar_done2').hide();
        $('#import_progressbar_done2').width('0%');
    }
    if (total > 0) {
        $('#import_progress_div').show();
        if (moreStep == 0 || currentPosition > 0) {
            $('#import_progression_details').html(currentPosition + '/' + total);
        }
        if (moreStep == 0) {
            $('#import_progressbar_done').width(progressionDone + '%');
            $('#import_progression_done').html(parseInt(progressionDone));
            $('#import_progressbar_next').width((progressionNext - progressionDone) + '%');
        } else {
            $('#import_progressbar_next').width('0%');
            $('#import_progressbar_done').width((100 - progressionDone) + '%');
            $('#import_progressbar_done2').show();
            $('#import_progressbar_done2').width(progressionDone + '%');
            if (moreStepLabel) {
                $('#import_progressbar_done2 span').html(moreStepLabel);
            }
        }
    }

    if (finish) {
        $('#import_progressbar_done').parent().removeClass('active progress-striped');
        $('#import_details_post_limit').hide();
        $('#import_details_progressing').hide();
        setTimeout(function () {
            $('#import_progressbar_done2').fadeOut();
        }, 1000);
        $('#import_progressbar_done').width(progressionDone + '%');
        $('#import_details_finished').show();
        $('#importProgress').modal({keyboard: true, closable: true});
        $('#import_stop_button').hide();
        $('#import_close_button').show();
    }
}

function updateValidationError(message, forWarnings) {
    $('#import_details_progressing').hide();
    $('#import_details_finished').hide();
    if (forWarnings) {
        $('#import_details_warning ul').append(message);
        $('#import_details_warning').show();
    } else {
        $('#import_details_error ul').append(message);
        $('#import_details_error').show();

        $('#validate_progressbar_next').addClass('progress-bar-danger');
        $('#validate_progressbar_next').removeClass('progress-bar-info');

        $('#import_stop_button').hide();
        $('#import_close_button').show();
    }
}

function updateValidationInfo(message) {
    $('#import_details_progressing').hide();
    $('#import_details_finished').hide();
    $('#import_details_info ul').append(message);
    $('#import_details_info').show();
}

function updateProgressionError(message, forWarnings) {
    $('#import_details_progressing').hide();
    $('#import_details_finished').hide();
    if (forWarnings) {
        $('#import_details_warning ul').append(message);
        $('#import_details_warning').show();
    } else {
        $('#import_details_error ul').append(message);
        $('#import_details_error').show();

        $('#import_progressbar_next').addClass('progress-bar-danger');
        $('#import_progressbar_next').removeClass('progress-bar-success');

        $('#import_stop_button').hide();
        $('#import_close_button').show();
    }
}

function html_escape(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}