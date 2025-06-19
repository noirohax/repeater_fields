$(document).ready(function() {
    var repeaterIndex = 1000;

    // Initialize existing repeater fields
    initializeRepeaterFields();

    function initializeRepeaterFields() {
        // This function would be called to set up existing fields
        // For now, we'll handle it in the form rendering
    }

    // Add new repeater field
    $(document).on('click', '.add-repeater-field', function() {
        var container = $(this).closest('.repeater-field-container');
        var fieldId = container.data('field-id');
        var template = $('.repeater-field-template[data-field-id="' + fieldId + '"]');
        
        if (template.length) {
            var newField = template.html();
            newField = newField.replace(/__INDEX__/g, repeaterIndex);
            
            // Hide the add button on the current field
            $(this).hide();
            
            // Append new field before the template
            template.before(newField);
            
            repeaterIndex++;
        }
    });

    // Remove repeater field
    $(document).on('click', '.remove-repeater-field', function() {
        var fieldGroup = $(this).closest('.repeater-field-group');
        var container = $(this).closest('.repeater-field-container');
        
        fieldGroup.remove();
        
        // Show add button on the last visible field if no add button is visible
        var visibleGroups = container.find('.repeater-field-group:visible');
        if (visibleGroups.length > 0 && container.find('.add-repeater-field:visible').length === 0) {
            visibleGroups.last().find('.add-repeater-field').show();
        }
    });

    // Handle form submission to collect repeater values
    $('form').on('submit', function() {
        $('.repeater-field-container').each(function() {
            var container = $(this);
            var fieldId = container.data('field-id');
            var values = [];
            
            container.find('.repeater-field-group:visible').each(function() {
                var group = $(this);
                var input = group.find('input, textarea, select').first();
                if (input.length) {
                    var value = input.val();
                    if (value && value.trim() !== '') {
                        values.push(value.trim());
                    }
                }
            });
            
            // Create hidden input with JSON data
            if (values.length > 0) {
                var hiddenInput = $('<input type="hidden" name="repeater_field_' + fieldId + '" />');
                hiddenInput.val(JSON.stringify(values));
                container.append(hiddenInput);
            }
        });
    });

    // Custom field form enhancement
    if ($('#custom-field-modal').length) {
        // Add help text for repeater option
        $('#is_repeater').on('change', function() {
            if ($(this).is(':checked')) {
                if (!$('.repeater-help-text').length) {
                    $(this).closest('.checkbox').after('<div class="alert alert-info repeater-help-text"><small>When enabled, users will see + and - buttons to add/remove multiple values for this field.</small></div>');
                }
            } else {
                $('.repeater-help-text').remove();
            }
        });
    }
});
