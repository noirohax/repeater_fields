<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="tw-max-w-4xl tw-mx-auto">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                    <?= e($title); ?>
                </h4>
                <?php if (isset($custom_field)) { ?>
                <a href="<?= admin_url('custom_fields/field'); ?>"
                    class="btn btn-primary">
                    <?= _l('new_custom_field'); ?>
                </a>
                <?php } ?>
            </div>
            <?= form_open($this->uri->uri_string()); ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div
                        class="company_field_info mbot25 alert alert-info<?= isset($custom_field) && $custom_field->fieldto != 'company' || ! isset($custom_field) ? ' hide' : ''; ?>">
                        <?= _l('custom_field_info_format_embed_info', [
                            _l('custom_field_company'),
                            '<a href="' . admin_url('settings?group=company#settings[company_info_format]') . '" class="alert-link" target="_blank">' . admin_url('settings?group=company') . '</a>',
                        ]); ?>
                    </div>
                    <div
                        class="customers_field_info mbot25 alert alert-info<?= isset($custom_field) && $custom_field->fieldto != 'customers' || ! isset($custom_field) ? ' hide' : ''; ?>">
                        <?= _l('custom_field_info_format_embed_info', [
                            _l('clients'),
                            '<a href="' . admin_url('settings?group=clients#settings[customer_info_format]') . '" class="alert-link" target="_blank">' . admin_url('settings?group=clients') . '</a>',
                        ]); ?>
                    </div>
                    <div
                        class="items_field_info mbot25 alert alert-warning<?= isset($custom_field) && $custom_field->fieldto != 'items' || ! isset($custom_field) ? ' hide' : ''; ?>">
                        Custom fields for items can't be included in calculation of totals.
                    </div>
                    <div
                        class="proposal_field_info mbot25 alert alert-info<?= isset($custom_field) && $custom_field->fieldto != 'proposal' || ! isset($custom_field) ? ' hide' : ''; ?>">
                        <?= _l('custom_field_info_format_embed_info', [
                            _l('proposals'),
                            '<a href="' . admin_url('settings?group=sales&tab=proposals#settings[proposal_info_format]') . '" class="alert-link" target="_blank">' . admin_url('settings?group=sales&tab=proposals') . '</a>',
                        ]); ?>
                    </div>

                    <?php $disable = isset($custom_field) && total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $custom_field->id, 'fieldto' => $custom_field->fieldto]) > 0 ? 'disabled' : ''; ?>

                    <div class="select-placeholder form-group">
                        <label
                            for="fieldto"><?= _l('custom_field_add_edit_belongs_top'); ?></label>
                        <select name="fieldto" id="fieldto" class="selectpicker" data-width="100%"
                            <?= e($disable); ?>
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            <option value=""></option>
                            <option value="company" <?= isset($custom_field) && $custom_field->fieldto == 'company' ? 'selected' : '' ?>>
                                <?= _l('custom_field_company'); ?>
                            </option>
                            <option value="leads" <?= isset($custom_field) && $custom_field->fieldto == 'leads' ? 'selected' : '' ?>>
                                <?= _l('custom_field_leads'); ?>
                            </option>
                            <option value="customers" <?= isset($custom_field) && $custom_field->fieldto == 'customers' ? 'selected' : '' ?>>
                                <?= _l('custom_field_customers'); ?>
                            </option>
                            <option value="contacts" <?= isset($custom_field) && $custom_field->fieldto == 'contacts' ? 'selected' : '' ?>>
                                <?= _l('custom_field_contacts'); ?>
                            </option>
                            <option value="staff" <?= isset($custom_field) && $custom_field->fieldto == 'staff' ? 'selected' : '' ?>>
                                <?= _l('custom_field_staff'); ?>
                            </option>
                            <option value="contracts" <?= isset($custom_field) && $custom_field->fieldto == 'contracts' ? 'selected' : '' ?>>
                                <?= _l('custom_field_contracts'); ?>
                            </option>
                            <option value="tasks" <?= isset($custom_field) && $custom_field->fieldto == 'tasks' ? 'selected' : '' ?>>
                                <?= _l('custom_field_tasks'); ?>
                            </option>
                            <option value="expenses" <?= isset($custom_field) && $custom_field->fieldto == 'expenses' ? 'selected' : '' ?>>
                                <?= _l('custom_field_expenses'); ?>
                            </option>
                            <option value="invoice" <?= isset($custom_field) && $custom_field->fieldto == 'invoice' ? 'selected' : '' ?>>
                                <?= _l('custom_field_invoice'); ?>
                            </option>
                            <option value="items" <?= isset($custom_field) && $custom_field->fieldto == 'items' ? 'selected' : '' ?>>
                                <?= _l('items'); ?>
                            </option>
                            <option value="credit_note" <?= isset($custom_field) && $custom_field->fieldto == 'credit_note' ? 'selected' : '' ?>>
                                <?= _l('credit_note'); ?>
                            </option>
                            <option value="estimate" <?= isset($custom_field) && $custom_field->fieldto == 'estimate' ? 'selected' : '' ?>>
                                <?= _l('custom_field_estimate'); ?>
                            </option>
                            <option value="proposal" <?= isset($custom_field) && $custom_field->fieldto == 'proposal' ? 'selected' : '' ?>>
                                <?= _l('proposal'); ?>
                            </option>
                            <option value="projects" <?= isset($custom_field) && $custom_field->fieldto == 'projects' ? 'selected' : '' ?>>
                                <?= _l('projects'); ?>
                            </option>
                            <option value="tickets" <?= isset($custom_field) && $custom_field->fieldto == 'tickets' ? 'selected' : '' ?>>
                                <?= _l('tickets'); ?>
                            </option>

                            <?php hooks()->do_action('after_custom_fields_select_options', $custom_field ?? null); ?>
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <?php $value = (isset($custom_field) ? $custom_field->name : ''); ?>
                    <?= render_input('name', 'custom_field_name', $value); ?>
                    <div class="select-placeholder form-group">
                        <label
                            for="type"><?= _l('custom_field_add_edit_type'); ?></label>
                        <select name="type" id="type" class="selectpicker"
                            <?= isset($custom_field) && total_rows(db_prefix() . 'customfieldsvalues', ['fieldid' => $custom_field->id, 'fieldto' => $custom_field->fieldto]) > 0 ? 'disabled' : ''; ?>
                            data-width="100%"
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                            data-hide-disabled="true">
                            <option value=""></option>
                            <option value="input" <?= isset($custom_field) && $custom_field->type == 'input' ? 'selected' : '' ?>>Input
                            </option>
                            <option value="number" <?= isset($custom_field) && $custom_field->type == 'number' ? 'selected' : '' ?>>Number
                            </option>
                            <option value="textarea" <?= isset($custom_field) && $custom_field->type == 'textarea' ? 'selected' : '' ?>>Textarea
                            </option>
                            <option value="select" <?= isset($custom_field) && $custom_field->type == 'select' ? 'selected' : '' ?>>Select
                            </option>
                            <option value="multiselect" <?= isset($custom_field) && $custom_field->type == 'multiselect' ? 'selected' : '' ?>>Multi
                                Select</option>
                            <option value="checkbox" <?= isset($custom_field) && $custom_field->type == 'checkbox' ? 'selected' : '' ?>>Checkbox
                            </option>
                            <option value="date_picker" <?= isset($custom_field) && $custom_field->type == 'date_picker' ? 'selected' : '' ?>>Date
                                Picker</option>
                            <option value="date_picker_time" <?= isset($custom_field) && $custom_field->type == 'date_picker_time' ? 'selected' : '' ?>>Datetime
                                Picker</option>
                            <option value="colorpicker" <?= isset($custom_field) && $custom_field->type == 'colorpicker' ? 'selected' : '' ?>>Color
                                Picker</option>
                            <option value="link" <?= isset($custom_field) && $custom_field->type == 'link' ? 'selected' : '' ?>
                                <?= isset($custom_field) && $custom_field->fieldto == 'items' ? 'disabled' : '' ?>
                                >Hyperlink
                            </option>
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <div id="options_wrapper"
                        class="<?= ! isset($custom_field) || isset($custom_field) && $custom_field->type != 'select' && $custom_field->type != 'checkbox' && $custom_field->type != 'multiselect' ? 'hide' : ''; ?>">
                        <span class="pull-left fa-regular fa-circle-question" data-toggle="tooltip"
                            title="<?= _l('custom_field_add_edit_options_tooltip'); ?>"></span>
                        <?php $value = (isset($custom_field) ? $custom_field->options : ''); ?>
                        <?= render_textarea('options', 'custom_field_add_edit_options', $value, ['rows' => 3]); ?>
                    </div>
                    <div id="default-value-field">
                        <?php
                            $value = (isset($custom_field) ? $custom_field->default_value : '');

echo render_textarea(
    isset($custom_field) && $custom_field->type === 'textarea' ? 'default_value' : '',
    'custom_field_add_edit_default_value',
    $value,
    [],
    [],
    'default-value-textarea-input' . (isset($custom_field) && ($custom_field->type !== 'textarea' || $custom_field->type === 'link') ? ' hide' : ''),
    'default-value'
);

echo render_input(
    isset($custom_field) && ! in_array($custom_field->type, ['textarea', 'link']) ? 'default_value' : '',
    'custom_field_add_edit_default_value',
    $value,
    'text',
    [],
    [],
    'default-value-text-input' . (isset($custom_field) && ($custom_field->type == 'link' || $custom_field->type === 'textarea') ? ' hide' : ''),
    'default-value'
);
?>
                    </div>
                    <div id="default-value-error" class="hide alert alert-danger"></div>
                    <?php $value = (isset($custom_field) ? $custom_field->field_order : ''); ?>
                    <?= render_input('field_order', 'custom_field_add_edit_order', $value, 'number'); ?>
                    <div class="form-group">
                        <label
                            for="bs_column"><?= _l('custom_field_column'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">col-md-</span>
                            <input type="number" max="12" class="form-control" name="bs_column" id="bs_column"
                                value="<?= ! isset($custom_field) ? 12 : $custom_field->bs_column; ?>">
                        </div>
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="disabled" id="disabled"
                            <?= isset($custom_field) && $custom_field->active == 0 ? 'checked' : '' ?>>
                        <label
                            for="disabled"><?= _l('custom_field_add_edit_disabled'); ?></label>
                    </div>

                    <div
                        class="display-inline-checkbox checkbox checkbox-primary<?= ! isset($custom_field) || (isset($custom_field) && $custom_field->type != 'checkbox') ? ' hide' : '' ?>">
                        <input type="checkbox" value="1" name="display_inline" id="display_inline"
                            <?= isset($custom_field) && $custom_field->display_inline == 1 ? 'checked' : '' ?>>
                        <label
                            for="display_inline"><?= _l('display_inline'); ?></label>
                    </div>

    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="is_repeater" id="is_repeater" value="1"
                <?php if (isset($custom_field) && $custom_field->is_repeater == 1) echo 'checked'; ?>>
                        <label
                            for="repeater">            Enable as repeater field
</label>
                    </div>


                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="only_admin" id="only_admin"
                            <?= isset($custom_field) && $custom_field->only_admin == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items') ? 'disabled' : '' ?>>
                        <label
                            for="only_admin"><?= _l('custom_field_only_admin'); ?></label>
                    </div>

                    <div
                        class="checkbox checkbox-primary disalow_client_to_edit <?= ! isset($custom_field) || (isset($custom_field) && ! in_array($custom_field->fieldto, $client_portal_fields)) || (isset($custom_field) && ! in_array($custom_field->fieldto, $client_editable_fields)) ? 'hide' : '' ?>">
                        <input type="checkbox" name="disalow_client_to_edit" id="disalow_client_to_edit"
                            <?= isset($custom_field) && $custom_field->disalow_client_to_edit == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1') ? 'disabled' : '' ?>>
                        <label
                            for="disalow_client_to_edit"><?= _l('custom_field_disallow_customer_to_edit'); ?></label>
                    </div>

                    <div class="checkbox checkbox-primary" id="required_wrap">
                        <input type="checkbox" name="required" id="required"
                            <?= isset($custom_field) && $custom_field->required == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && $custom_field->fieldto == 'company' ? 'disabled' : '' ?>>
                        <label
                            for="required"><?= _l('custom_field_required'); ?></label>
                    </div>

                    <p class="bold">
                        <?= _l('custom_field_visibility'); ?>
                    </p>

                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="show_on_table" id="show_on_table"
                            <?= isset($custom_field) && $custom_field->show_on_table == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items') ? 'disabled' : '' ?>>
                        <label
                            for="show_on_table"><?= _l('custom_field_show_on_table'); ?></label>
                    </div>

                    <div
                        class="checkbox checkbox-primary show-on-pdf <?= ! isset($custom_field) || (isset($custom_field) && ! in_array($custom_field->fieldto, $pdf_fields)) ? 'hide' : '' ?>">
                        <input type="checkbox" name="show_on_pdf" id="show_on_pdf"
                            <?= isset($custom_field) && $custom_field->show_on_pdf == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->fieldto == 'items') ? 'disabled' : '' ?>>
                        <label for="show_on_pdf"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
                                data-title="<?= _l('custom_field_pdf_html_help'); ?>"></i>
                            <?= _l('custom_field_show_on_pdf'); ?></label>
                    </div>

                    <div
                        class="checkbox checkbox-primary show-on-client-portal <?= ! isset($custom_field) || (isset($custom_field) && ! in_array($custom_field->fieldto, $client_portal_fields)) ? 'hide' : '' ?>">
                        <input type="checkbox" name="show_on_client_portal" id="show_on_client_portal"
                            <?= isset($custom_field) && $custom_field->show_on_client_portal == 1 ? 'checked' : '' ?>
                        <?= isset($custom_field) && ($custom_field->fieldto == 'company' || $custom_field->only_admin == '1') ? 'disabled' : '' ?>>
                        <label for="show_on_client_portal"><i class="fa-regular fa-circle-question"
                                data-toggle="tooltip"
                                data-title="<?= _l('custom_field_show_on_client_portal_help'); ?>"></i>
                            <?= _l('custom_field_show_on_client_portal'); ?></label>
                    </div>

                    <div
                        class="show-on-ticket-form checkbox checkbox-primary<?= ! isset($custom_field) || (isset($custom_field) && $custom_field->fieldto != 'tickets') ? ' hide' : '' ?>">
                        <input type="checkbox" value="1" name="show_on_ticket_form" id="show_on_ticket_form"
                            <?= isset($custom_field) && $custom_field->show_on_ticket_form == 1 ? 'checked' : '' ?>>
                        <label
                            for="show_on_ticket_form"><?= _l('show_on_ticket_form'); ?></label>
                    </div>

                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary" id="submitForm">
                        <?= _l('submit'); ?>
                    </button>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var pdf_fields = <?= json_encode($pdf_fields); ?> ;
    var
        client_portal_fields = <?= json_encode($client_portal_fields); ?> ;
    var
        client_editable_fields = <?= json_encode($client_editable_fields); ?> ;

    $(function() {
        appValidateForm($('form'), {
            fieldto: 'required',
            name: 'required',
            type: 'required',
            bs_column: 'required',
            options: {
                required: {
                    depends: function(element) {
                        return ['select', 'checkbox', 'multiselect'].indexOf($('#type').val()) > -1
                    }
                }
            }
        }, function(form) {
            validateDefaultValueField().then(function(validation) {
                if (validation.valid) {
                    $('#fieldto,#type').prop('disabled', false);

                    $.post(form.action, $(form).serialize(), function(data) {
                        window.location.href = admin_url + 'custom_fields/field/' + data
                            .id;
                    }, 'json');
                }
            });

            return false;
        });

        $('select[name="fieldto"]').on('change', function() {
            var field = $(this).val();

            $.inArray(field, pdf_fields) !== -1 ? $('.show-on-pdf').removeClass('hide') : $(
                    '.show-on-pdf')
                .addClass('hide');

            if ($.inArray(field, client_portal_fields) !== -1) {
                $('.show-on-client-portal').removeClass('hide');
                $('.disalow_client_to_edit').removeClass('hide');

                if ($.inArray(field, client_editable_fields) !== -1) {
                    $('.disalow_client_to_edit').removeClass('hide');
                } else {
                    $('.disalow_client_to_edit').addClass('hide');
                    $('.disalow_client_to_edit input').prop('checked', false);
                }
            } else {
                $('.show-on-client-portal').addClass('hide');
                $('.disalow_client_to_edit').addClass('hide');
            }
            if (field == 'tickets') {
                $('.show-on-ticket-form').removeClass('hide');
            } else {
                $('.show-on-ticket-form').addClass('hide');
                $('.show-on-ticket-form input').prop('checked', false);
            }

            field == 'customers' ? $('.customers_field_info').removeClass('hide') : $(
                '.customers_field_info').addClass('hide');
            field == 'items' ? $('.items_field_info').removeClass('hide') : $('.items_field_info')
                .addClass(
                    'hide');
            field == 'company' ? $('.company_field_info').removeClass('hide') : $('.company_field_info')
                .addClass('hide');
            field == 'proposal' ? $('.proposal_field_info').removeClass('hide') : $(
                    '.proposal_field_info')
                .addClass('hide');

            if (field == 'company') {
                $('#only_admin').prop('disabled', true).prop('checked', false);
                $('input[name="required"]').prop('disabled', true).prop('checked', false);
                $('#show_on_table').prop('disabled', true).prop('checked', false);
                $('#show_on_client_portal').prop('disabled', true).prop('checked', true);
            } else if (field == 'items') {
                $('#type option[value="link"]').prop('disabled', true);
                $('#show_on_table').prop('disabled', true).prop('checked', true);
                $('#show_on_pdf').prop('disabled', true).prop('checked', true);
                $('#only_admin').prop('disabled', true).prop('checked', false);
            } else {
                $('#only_admin').prop('disabled', false).prop('checked', false);
                $('input[name="required"]').prop('disabled', false).prop('checked', false);
                $('#show_on_table').prop('disabled', false).prop('checked', false);
                $('#show_on_client_portal').prop('disabled', false).prop('checked', false);
                $('#show_on_pdf').prop('disabled', false).prop('checked', false);
                $('#type option[value="link"]').prop('disabled', false);
            }
            $('#type').selectpicker('refresh');
        });

        $('select[name="type"]').on('change', function() {
            var type = $(this).val();
            var options_wrapper = $('#options_wrapper');
            var display_inline = $('.display-inline-checkbox')
            var default_value = $('#default-value-field');

            $('textarea.default-value, input.default-value').val('');

            if (type !== 'link' && type !== 'textarea') {
                $('textarea.default-value').removeAttr('name');
                $('input.default-value').attr('name', 'default_value');
                $('.default-value-textarea-input').addClass('hide');
                $('.default-value-text-input').removeClass('hide');
            }

            if (type == 'select' || type == 'checkbox' || type == 'multiselect') {
                options_wrapper.removeClass('hide');
                if (type == 'checkbox') {
                    display_inline.removeClass('hide');
                } else {
                    display_inline.addClass('hide');
                    display_inline.find('input').prop('checked', false);
                }
            } else if (type === 'link') {
                default_value.addClass('hide');
            } else if (type === 'textarea') {
                $('textarea.default-value').attr('name', 'default_value');
                $('input.default-value').removeAttr('name');
                $('.default-value-textarea-input').removeClass('hide');
                $('.default-value-text-input').addClass('hide');
            } else {
                options_wrapper.addClass('hide');
                display_inline.addClass('hide');
                default_value.removeClass('hide')
                display_inline.find('input').prop('checked', false);
            }

            validateDefaultValueField();
        });

        $('body').on('change', 'input[name="only_admin"]', function() {
            $('#show_on_client_portal').prop('disabled', $(this).prop('checked')).prop('checked',
                false);
            $('#disalow_client_to_edit').prop('disabled', $(this).prop('checked')).prop('checked',
                false);
        });

        $('body').on('blur', '[name="default_value"], #options', function() {
            validateDefaultValueField();
        });
    });

    function validateDefaultValueField() {

        var value = $('[name="default_value"]').val();
        var type = $('#type').val();

        var message = '';
        var valid = jQuery.Deferred();
        var $error = $('#default-value-error');
        var $label = $('label[for="default_value"]');
        $label.find('.sample').remove();

        if (type == '') {
            $error.addClass('hide');
            return;
        }

        if (value) {
            value = value.trim();
        }

        switch (type) {
            case 'input':
            case 'link':
            case 'textarea':
                valid.resolve({
                    valid: true,
                });
                break;
            case 'number':
                valid.resolve({
                    valid: value === '' ? true : new RegExp(/^-?(?:\d+|\d*\.\d+)$/).test(value),
                    message: 'Enter a valid number.',
                });
                break;
            case 'multiselect':
            case 'checkbox':
            case 'select':
                if (value === '') {
                    valid.resolve({
                        valid: true,
                    });
                } else {
                    var defaultOptions = value.split(',')
                        .map(function(option) {
                            return option.trim();
                        }).filter(function(option) {
                            return option !== ''
                        });

                    if (type === 'select' && defaultOptions.length > 1) {
                        valid.resolve({
                            valid: true,
                            message: 'You cannot have multiple options selected on "Select" field type.',
                        });
                    } else {
                        var availableOptions = $('#options').val().split(',')
                            .map(function(option) {
                                return option.trim();
                            }).filter(function(option) {
                                return option !== ''
                            });

                        var nonExistentOptions = defaultOptions.filter(function(i) {
                            return availableOptions.indexOf(i) < 0;
                        });

                        valid.resolve({
                            valid: nonExistentOptions.length === 0,
                            message: nonExistentOptions.join(',') +
                                ' options are not available in the options field.',
                        });
                    }
                }

                break;
            case 'date_picker':
            case 'date_picker_time':

                if (value !== '') {
                    $.post(admin_url + 'custom_fields/validate_default_date', {
                        date: value,
                        type: type,
                    }, function(data) {
                        valid.resolve({
                            valid: data.valid,
                            message: 'Enter date in ' + (type === 'date_picker' ? 'Y-m-d' :
                                    'Y-m-d H:i') +
                                ' format or English date format for the PHP "<a href=\'https://www.php.net/manual/en/function.strtotime.php\'" target="_blank">strtotime</a> function.',
                        });

                        if (data.valid) {
                            $label.append(' <small class="sample">Sample: ' + data.sample + '</small>');
                        }
                    }, 'json');
                } else {
                    valid.resolve({
                        valid: true,
                    });
                }

                break;
            case 'colorpicker':
                valid.resolve({
                    valid: value === '' ? true : new RegExp(/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/gm).test(value),
                    message: 'Enter color in HEX format, for example: #f2dede',
                })
                break;
        }

        valid.done(function(validation) {
            $('#submitForm').prop('disabled', !validation.valid);
            validation.valid ? $error.addClass('hide') : $error.removeClass('hide');
            $error.html(validation.message);
        });

        return valid;
    }
</script>
</body>

</html>