document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessages = [];

        // Required numeric fields
        const numericFields = form.querySelectorAll('input[type="number"]');
        numericFields.forEach(function(field) {
            if (field.value === '') {
                isValid = false;
                const label = field.closest('.field-container')?.querySelector('label')?.textContent || field.name;
                errorMessages.push(label + ' is required.');
            } else if (isNaN(parseFloat(field.value))) {
                isValid = false;
                const label = field.closest('.field-container')?.querySelector('label')?.textContent || field.name;
                errorMessages.push(label + ' must be a number.');
            }
        });

        // Specific range validations if needed
        const weeksToShow = form.querySelector('input[name="config_weeks__weeks_to_show"]');
        if (weeksToShow && (parseInt(weeksToShow.value) < 1 || parseInt(weeksToShow.value) > 52)) {
            isValid = false;
            errorMessages.push('Weeks to show must be between 1 and 52.');
        }

        const thresholds = [
            'config_display__achievement_tier_green',
            'config_display__achievement_tier_yellow',
            'config_display__achievement_tier_orange'
        ];
        thresholds.forEach(function(name) {
            const field = form.querySelector('input[name="' + name + '"]');
            if (field && (parseFloat(field.value) < 0 || parseFloat(field.value) > 500)) {
                isValid = false;
                errorMessages.push('Achievement thresholds must be between 0 and 500%.');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please correct the following errors:\n\n' + errorMessages.join('\n'));
        }
    });
});