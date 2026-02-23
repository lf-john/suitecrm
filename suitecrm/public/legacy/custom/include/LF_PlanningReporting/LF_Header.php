<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_Header - Shared header component for LF Planning & Reporting module pages.
 *
 * Provides a consistent branded header with LF logo, navigation, and page title
 * across all module pages (Dashboard, Planning, Reporting, Config, Rep Targets).
 *
 * @package LF_PlanningReporting
 */
class LF_Header
{
    /**
     * Render the LF branded header
     *
     * @param string $pageTitle The title to display (e.g., "Weekly Planning Dashboard")
     * @param string $activeNav The active navigation item ('dashboard', 'planning', 'reporting', 'config', 'targets')
     * @param array $options Optional settings (showWeekSelector, showViewToggle, weekList, viewMode, reps, etc.)
     */
    public static function render($pageTitle, $activeNav = '', $options = [])
    {
        $baseLegacyUrl = 'index.php';

        // Navigation items
        $navItems = [
            'dashboard' => ['label' => 'Planning Dashboard', 'url' => $baseLegacyUrl . '?module=LF_WeeklyPlan&action=dashboard'],
            'planning' => ['label' => 'My Planning', 'url' => $baseLegacyUrl . '?module=LF_WeeklyPlan&action=planning'],
            'reporting' => ['label' => 'Reporting Dashboard', 'url' => $baseLegacyUrl . '?module=LF_WeeklyPlan&action=reporting'],
            'targets' => ['label' => 'Rep Targets', 'url' => $baseLegacyUrl . '?module=LF_RepTargets&action=manage', 'admin' => true],
            'config' => ['label' => 'Configuration', 'url' => $baseLegacyUrl . '?module=LF_PRConfig&action=config', 'admin' => true],
        ];

        global $current_user;
        $isAdmin = $current_user->is_admin ?? false;

        echo '<div class="lf-branded-header">';

        // Top bar with logo and nav
        echo '<div class="lf-header-top">';

        // Logo section
        echo '<div class="lf-logo-section">';
        echo '<div class="lf-logo">';
        echo '<span class="lf-logo-icon">LF</span>';
        echo '<span class="lf-logo-text">Logical Front</span>';
        echo '</div>';
        echo '</div>';

        // Navigation
        echo '<nav class="lf-nav">';
        foreach ($navItems as $key => $item) {
            // Skip admin-only items for non-admins
            if (!empty($item['admin']) && !$isAdmin) {
                continue;
            }
            $activeClass = ($key === $activeNav) ? ' active' : '';
            echo '<a href="' . htmlspecialchars($item['url']) . '" class="lf-nav-item' . $activeClass . '">' . htmlspecialchars($item['label']) . '</a>';
        }
        echo '</nav>';

        echo '</div>'; // end lf-header-top

        // Title bar with page title and optional controls
        echo '<div class="lf-header-title-bar">';
        echo '<div class="lf-title-section">';
        echo '<h1 class="lf-page-title">' . htmlspecialchars(strtoupper($pageTitle)) . '</h1>';
        echo '</div>';

        // Optional controls section
        if (!empty($options['showViewToggle']) || !empty($options['showWeekSelector'])) {
            echo '<div class="lf-controls-section">';

            // View Toggle (Manager/Territory or Team/Rep)
            if (!empty($options['showViewToggle'])) {
                $viewMode = $options['viewMode'] ?? 'team';
                echo '<div class="lf-view-toggle">';
                echo '<button class="lf-view-button' . ($viewMode === 'team' ? ' active' : '') . '" data-view="team">Manager View</button>';
                echo '<button class="lf-view-button' . ($viewMode === 'rep' ? ' active' : '') . '" data-view="rep">Territory View</button>';
                echo '</div>';

                // Rep selector (hidden by default, shown in territory view)
                if (!empty($options['reps'])) {
                    $selectedRepId = $options['selectedRepId'] ?? '';
                    $hideClass = $viewMode === 'team' ? ' hidden' : '';
                    echo '<select id="lf-rep-selector" class="lf-rep-dropdown' . $hideClass . '">';
                    echo '<option value="">Select Rep...</option>';
                    foreach ($options['reps'] as $rep) {
                        $repId = $rep['assigned_user_id'] ?? '';
                        $repName = trim(($rep['first_name'] ?? '') . ' ' . ($rep['last_name'] ?? ''));
                        $selected = ($repId === $selectedRepId) ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($repId) . '"' . $selected . '>' . htmlspecialchars($repName) . '</option>';
                    }
                    echo '</select>';
                }
            }

            // Week Selector
            if (!empty($options['showWeekSelector']) && !empty($options['weekList'])) {
                $currentWeek = $options['currentWeek'] ?? '';
                echo '<div class="lf-week-selector">';
                echo '<button class="lf-week-nav-btn" id="lf-week-back">&larr;</button>';
                echo '<select id="lf-week-select" class="lf-week-dropdown">';
                foreach ($options['weekList'] as $week) {
                    $selected = ($week['weekStart'] === $currentWeek) ? ' selected' : '';
                    $marker = !empty($week['isCurrent']) ? ' *' : '';
                    echo '<option value="' . htmlspecialchars($week['weekStart']) . '"' . $selected . '>' . htmlspecialchars($week['label'] . $marker) . '</option>';
                }
                echo '</select>';
                echo '<button class="lf-week-nav-btn" id="lf-week-next">&rarr;</button>';
                echo '<button class="lf-week-nav-btn lf-week-current" id="lf-week-current">Current</button>';
                echo '</div>';
            }

            echo '</div>'; // end lf-controls-section
        }

        echo '</div>'; // end lf-header-title-bar

        echo '</div>'; // end lf-branded-header
    }

    /**
     * Render CSS for the branded header (include once per page)
     */
    public static function renderCSS()
    {
        echo <<<'CSS'
<style>
/* LF Branded Header Styles */
.lf-branded-header {
    background: white;
    border-bottom: 3px solid var(--lf-primary, #125EAD);
    margin-bottom: 24px;
}

.lf-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    border-bottom: 1px solid var(--border-color, #edebe9);
}

.lf-logo-section {
    display: flex;
    align-items: center;
}

.lf-logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.lf-logo-icon {
    background: linear-gradient(135deg, #125EAD, #4BB74E);
    color: white;
    font-weight: 700;
    font-size: 18px;
    padding: 8px 12px;
    border-radius: 6px;
}

.lf-logo-text {
    font-size: 18px;
    font-weight: 600;
    color: #125EAD;
}

.lf-nav {
    display: flex;
    gap: 4px;
}

.lf-nav-item {
    padding: 10px 16px;
    color: #605e5c;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.lf-nav-item:hover {
    background: #f3f2f1;
    color: #125EAD;
}

.lf-nav-item.active {
    background: rgba(18, 94, 173, 0.1);
    color: #125EAD;
    font-weight: 600;
}

.lf-header-title-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.lf-title-section {
    display: flex;
    align-items: center;
}

.lf-page-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #125EAD;
    letter-spacing: 0.5px;
}

.lf-controls-section {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.lf-view-toggle {
    background: #f3f2f1;
    border-radius: 8px;
    padding: 4px;
    display: flex;
}

.lf-view-button {
    background: none;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    color: #605e5c;
    font-weight: 500;
    transition: all 0.2s ease;
}

.lf-view-button:hover:not(.active) {
    background: #edebe9;
}

.lf-view-button.active {
    background: #125EAD;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.lf-rep-dropdown {
    padding: 8px 12px;
    border: 1px solid #edebe9;
    border-radius: 6px;
    font-size: 14px;
    color: #323130;
    background: white;
    cursor: pointer;
    min-width: 180px;
}

.lf-rep-dropdown:focus {
    outline: none;
    border-color: #125EAD;
    box-shadow: 0 0 0 2px rgba(18, 94, 173, 0.1);
}

.lf-rep-dropdown.hidden {
    display: none;
}

.lf-week-selector {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lf-week-dropdown {
    padding: 8px 12px;
    border: 1px solid #edebe9;
    border-radius: 6px;
    font-size: 14px;
    color: #323130;
    background: white;
    cursor: pointer;
    min-width: 200px;
}

.lf-week-dropdown:focus {
    outline: none;
    border-color: #125EAD;
    box-shadow: 0 0 0 2px rgba(18, 94, 173, 0.1);
}

.lf-week-nav-btn {
    background: white;
    border: 1px solid #edebe9;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    color: #605e5c;
    transition: all 0.2s ease;
}

.lf-week-nav-btn:hover {
    background: #f3f2f1;
    border-color: #125EAD;
    color: #125EAD;
}

.lf-week-nav-btn.lf-week-current {
    background: #4BB74E;
    color: white;
    border-color: #4BB74E;
}

.lf-week-nav-btn.lf-week-current:hover {
    background: #2F7D32;
    border-color: #2F7D32;
}

/* Responsive */
@media (max-width: 1024px) {
    .lf-header-top {
        flex-direction: column;
        gap: 12px;
    }

    .lf-nav {
        flex-wrap: wrap;
        justify-content: center;
    }

    .lf-header-title-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .lf-controls-section {
        width: 100%;
        justify-content: flex-start;
    }
}

@media (max-width: 768px) {
    .lf-nav-item {
        padding: 8px 12px;
        font-size: 13px;
    }

    .lf-page-title {
        font-size: 18px;
    }

    .lf-week-selector {
        flex-wrap: wrap;
    }

    .lf-week-dropdown {
        min-width: 160px;
    }
}
</style>
CSS;
    }
}
