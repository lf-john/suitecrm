<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_SubHeader - SuiteCRM 8-style sub-header component.
 *
 * Renders a teal gradient sub-header matching SuiteCRM 8 appearance.
 * Does NOT replace the standard SuiteCRM header - works alongside it.
 *
 * @package LF_PlanningReporting
 */
class LF_SubHeader
{
    /**
     * Render the SuiteCRM-style sub-header bar
     *
     * @param string $title The page title (e.g., "WEEKLY PLANNING")
     * @param array $options Optional controls to display
     */
    public static function render($title, $options = [])
    {
        echo '<div class="lf-subheader">';
        echo '<div class="lf-subheader-left">';
        echo '<h1 class="lf-subheader-title">' . htmlspecialchars(strtoupper($title)) . '</h1>';
        echo '</div>';

        // Right side controls
        if (!empty($options)) {
            echo '<div class="lf-subheader-right">';

            // User selector (for admins)
            if (!empty($options['showUserSelector']) && !empty($options['users'])) {
                $selectedUserId = $options['selectedUserId'] ?? '';
                echo '<div class="lf-control-group">';
                echo '<label class="lf-control-label">Viewing:</label>';
                echo '<select id="lf-user-selector" class="lf-subheader-select">';
                echo '<option value="">All Reps (Team View)</option>';
                foreach ($options['users'] as $user) {
                    $userId = $user['id'] ?? $user['assigned_user_id'] ?? '';
                    $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    $selected = ($userId === $selectedUserId) ? ' selected' : '';
                    echo '<option value="' . htmlspecialchars($userId) . '"' . $selected . '>' . htmlspecialchars($userName) . '</option>';
                }
                echo '</select>';
                echo '</div>';
            }

            // Week selector
            if (!empty($options['showWeekSelector']) && !empty($options['weekList'])) {
                $currentWeek = $options['currentWeek'] ?? '';
                echo '<div class="lf-control-group lf-week-controls">';
                echo '<button type="button" class="lf-subheader-btn" id="lf-week-back">&larr;</button>';
                echo '<select id="lf-week-select" class="lf-subheader-select">';
                foreach ($options['weekList'] as $week) {
                    $selected = ($week['weekStart'] === $currentWeek) ? ' selected' : '';
                    $marker = !empty($week['isCurrent']) ? ' *' : '';
                    echo '<option value="' . htmlspecialchars($week['weekStart']) . '"' . $selected . '>' . htmlspecialchars($week['label'] . $marker) . '</option>';
                }
                echo '</select>';
                echo '<button type="button" class="lf-subheader-btn" id="lf-week-next">&rarr;</button>';
                echo '<button type="button" class="lf-subheader-btn lf-btn-current" id="lf-week-current">Current</button>';
                echo '</div>';
            }

            echo '</div>'; // end lf-subheader-right
        }

        echo '</div>'; // end lf-subheader
    }

    /**
     * Render CSS for the sub-header (include once per page)
     */
    public static function renderCSS()
    {
        echo <<<'CSS'
<style>
/* Mockup-matching Sub-Header (white background, dark text) */
.lf-subheader {
    background: white;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 0;
    border-bottom: 1px solid #edebe9;
}

.lf-subheader-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.lf-subheader-title {
    color: #323130;
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.lf-subheader-right {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.lf-control-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lf-control-label {
    color: #605e5c;
    font-size: 13px;
    font-weight: 500;
}

.lf-subheader-select {
    padding: 8px 12px;
    border: 1px solid #edebe9;
    border-radius: 4px;
    background: white;
    color: #323130;
    font-size: 13px;
    cursor: pointer;
    min-width: 170px;
}

.lf-subheader-select:focus {
    outline: none;
    border-color: #125EAD;
    box-shadow: 0 0 0 2px rgba(18,94,173,0.2);
}

.lf-subheader-btn {
    padding: 8px 12px;
    border: 1px solid #edebe9;
    border-radius: 4px;
    background: white;
    color: #323130;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lf-subheader-btn:hover {
    background: #f3f2f1;
    border-color: #d2d0ce;
}

.lf-subheader-btn.lf-btn-current {
    background: white;
    border: 1px solid #125EAD;
    color: #125EAD;
    font-weight: 600;
}

.lf-week-controls {
    display: flex;
    gap: 4px;
}

/* Content wrapper with blue-to-green gradient background */
.lf-content-wrapper {
    background: linear-gradient(135deg, #125EAD 0%, #4BB74E 100%);
    min-height: calc(100vh - 150px);
    padding: 0;
    margin: 0;
}

.lf-content-wrapper.white-bg {
    background: white;
}

/* Ensure dropdowns appear above other content in iframe */
.lf-subheader-select {
    position: relative;
    z-index: 1000;
}

/* Responsive */
@media (max-width: 768px) {
    .lf-subheader {
        padding: 12px 16px;
    }

    .lf-subheader-title {
        font-size: 16px;
    }

    .lf-subheader-right {
        width: 100%;
        justify-content: flex-start;
    }

    .lf-subheader-select {
        min-width: 120px;
    }
}
</style>
CSS;
    }

    /**
     * Render JavaScript for controls
     */
    public static function renderJS()
    {
        echo <<<'JS'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User selector
    var userSelect = document.getElementById('lf-user-selector');
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            var url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('rep_id', this.value);
            } else {
                url.searchParams.delete('rep_id');
            }
            window.location.href = url.toString();
        });
    }

    // Week selector
    var weekSelect = document.getElementById('lf-week-select');
    var weekBack = document.getElementById('lf-week-back');
    var weekNext = document.getElementById('lf-week-next');
    var weekCurrent = document.getElementById('lf-week-current');

    if (weekSelect) {
        weekSelect.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('week_start', this.value);
            window.location.href = url.toString();
        });
    }

    if (weekBack && weekSelect) {
        weekBack.addEventListener('click', function() {
            if (weekSelect.selectedIndex > 0) {
                weekSelect.selectedIndex--;
                weekSelect.dispatchEvent(new Event('change'));
            }
        });
    }

    if (weekNext && weekSelect) {
        weekNext.addEventListener('click', function() {
            if (weekSelect.selectedIndex < weekSelect.options.length - 1) {
                weekSelect.selectedIndex++;
                weekSelect.dispatchEvent(new Event('change'));
            }
        });
    }

    if (weekCurrent && weekSelect) {
        weekCurrent.addEventListener('click', function() {
            for (var i = 0; i < weekSelect.options.length; i++) {
                if (weekSelect.options[i].text.indexOf('*') > -1) {
                    weekSelect.selectedIndex = i;
                    weekSelect.dispatchEvent(new Event('change'));
                    break;
                }
            }
        });
    }
});
</script>
JS;
    }
}
