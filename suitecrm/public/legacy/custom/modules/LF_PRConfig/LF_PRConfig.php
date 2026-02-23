<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_PRConfig Bean Class
 *
 * Configuration module for storing key-value configuration pairs
 * organized by category.
 *
 * Value encoding convention:
 *   - Plain values (strings, integers): stored as-is, retrieved with getConfig()
 *     Examples: '500000', '14', '5', '3-Confirmation (10%)'
 *   - JSON values (arrays, objects): stored as JSON strings, retrieved with getConfigJson()
 *     Examples: '["Cold Call","Referral"]', '{"2-Analysis (1%)":1,...}'
 *
 * @see install.php for the full list of default config entries
 */
#[\AllowDynamicProperties]
class LF_PRConfig extends SugarBean
{
    public $table_name = 'lf_pr_config';
    public $object_name = 'LF_PRConfig';
    public $module_name = 'LF_PRConfig';
    public $module_dir = 'LF_PRConfig';

    /**
     * Get a plain configuration value by category and config_name.
     *
     * Use this for values stored as plain strings or numbers.
     * For JSON-encoded values (arrays, objects), use getConfigJson().
     *
     * @param string $category The configuration category
     * @param string $configName The configuration key name
     * @return string|null The configuration value, or null if not found
     */
    public static function getConfig($category, $configName)
    {
        $db = DBManagerFactory::getInstance();

        $query = sprintf(
            "SELECT `value` FROM lf_pr_config WHERE category = %s AND config_name = %s AND deleted = 0",
            $db->quoted($category),
            $db->quoted($configName)
        );

        $result = $db->getOne($query);

        if ($result === false) {
            return null;
        }
        // SuiteCRM's DB layer HTML-encodes values (XSS protection)
        return html_entity_decode($result, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get a JSON-encoded configuration value, decoded to a PHP array.
     *
     * Use this for config values stored as JSON arrays or objects
     * (e.g., stage_order, pipeline_stages, stage_probabilities, source_types).
     *
     * @param string $category The configuration category
     * @param string $configName The configuration key name
     * @return array|null The decoded value, or null if not found
     */
    public static function getConfigJson($category, $configName)
    {
        $raw = self::getConfig($category, $configName);
        if ($raw === null) {
            return null;
        }
        $decoded = json_decode($raw, true);
        return $decoded;
    }

    /**
     * Get all configuration values as a nested array.
     *
     * Values are returned as raw strings -- caller is responsible
     * for json_decode() on JSON values.
     *
     * @return array Nested array: $result[$category][$configName] = $value (raw string)
     */
    public static function getAll()
    {
        $db = DBManagerFactory::getInstance();

        $query = "SELECT category, config_name, `value` FROM lf_pr_config WHERE deleted = 0";
        $result = $db->query($query);

        $config = [];
        while ($row = $db->fetchByAssoc($result)) {
            $config[$row['category']][$row['config_name']] = html_entity_decode($row['value'], ENT_QUOTES, 'UTF-8');
        }

        return $config;
    }
}
