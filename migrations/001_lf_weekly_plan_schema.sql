-- LF Weekly Plan Module — Custom Table Schema
-- Run against the suitecrm_db database.
-- All statements use CREATE TABLE IF NOT EXISTS / ALTER ... ADD COLUMN IF NOT EXISTS
-- so they are safe to re-run on a database that already has some of these tables.
--
-- Usage:
--   docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db \
--     < migrations/001_lf_weekly_plan_schema.sql

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. lf_weekly_plan
--    One row per rep per week. Tracks plan status and frozen KPI values once
--    the plan is submitted/reviewed.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_weekly_plan` (
  `id`                  char(36)        NOT NULL,
  `name`                varchar(255)    DEFAULT NULL,
  `date_entered`        datetime        DEFAULT NULL,
  `date_modified`       datetime        DEFAULT NULL,
  `modified_user_id`    char(36)        DEFAULT NULL,
  `created_by`          char(36)        DEFAULT NULL,
  `deleted`             tinyint(1)      DEFAULT '0',
  `assigned_user_id`    char(36)        NOT NULL,
  `week_start_date`     date            DEFAULT NULL,
  `status`              varchar(255)    DEFAULT 'in_progress',
  `submitted_date`      datetime        DEFAULT NULL,
  `reviewed_by`         char(36)        DEFAULT NULL,
  `reviewed_date`       datetime        DEFAULT NULL,
  `notes`               text,
  `frozen_closing`      decimal(26,6)   DEFAULT NULL,
  `frozen_progression`  decimal(26,6)   DEFAULT NULL,
  `frozen_new_pipeline` decimal(26,6)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_lf_weekly_plan_user_week` (`assigned_user_id`, `week_start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. lf_plan_op_items
--    Opportunity line items inside a weekly plan (closing, progression, at-risk).
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_plan_op_items` (
  `id`                  char(36)        NOT NULL,
  `name`                varchar(255)    DEFAULT NULL,
  `date_entered`        datetime        DEFAULT NULL,
  `date_modified`       datetime        DEFAULT NULL,
  `modified_user_id`    char(36)        DEFAULT NULL,
  `created_by`          char(36)        DEFAULT NULL,
  `deleted`             tinyint(1)      DEFAULT '0',
  `lf_weekly_plan_id`   char(36)        NOT NULL,
  `opportunity_id`      char(36)        NOT NULL,
  `item_type`           varchar(255)    DEFAULT NULL,
  `projected_stage`     varchar(100)    DEFAULT NULL,
  `planned_day`         varchar(255)    DEFAULT NULL,
  `plan_description`    text,
  `is_at_risk`          tinyint(1)      DEFAULT '0',
  `original_stage`      varchar(100)    DEFAULT NULL,
  `original_profit`     decimal(26,6)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_opportunity_id`  (`opportunity_id`),
  KEY `idx_plan_id`         (`lf_weekly_plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. lf_plan_prospect_items
--    New-pipeline prospect items inside a weekly plan.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_plan_prospect_items` (
  `id`                        char(36)        NOT NULL,
  `name`                      varchar(255)    DEFAULT NULL,
  `date_entered`              datetime        DEFAULT NULL,
  `date_modified`             datetime        DEFAULT NULL,
  `modified_user_id`          char(36)        DEFAULT NULL,
  `created_by`                char(36)        DEFAULT NULL,
  `deleted`                   tinyint(1)      DEFAULT '0',
  `lf_weekly_plan_id`         char(36)        NOT NULL,
  `source_type`               varchar(100)    DEFAULT NULL,
  `planned_day`               varchar(255)    DEFAULT NULL,
  `expected_value`            decimal(26,6)   DEFAULT NULL,
  `plan_description`          text,
  `status`                    varchar(255)    DEFAULT 'planned',
  `converted_opportunity_id`  char(36)        DEFAULT NULL,
  `prospecting_notes`         text,
  `expected_revenue`          decimal(26,6)   DEFAULT NULL,
  `expected_profit`           decimal(26,6)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lf_weekly_plan_id` (`lf_weekly_plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. lf_pr_config
--    Key/value config store for the Weekly Plan module (targets, thresholds, etc.)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_pr_config` (
  `id`               char(36)        NOT NULL,
  `name`             varchar(255)    DEFAULT NULL,
  `date_entered`     datetime        DEFAULT NULL,
  `date_modified`    datetime        DEFAULT NULL,
  `modified_user_id` char(36)        DEFAULT NULL,
  `created_by`       char(36)        DEFAULT NULL,
  `deleted`          tinyint(1)      DEFAULT '0',
  `category`         varchar(50)     DEFAULT NULL,
  `config_name`      varchar(100)    DEFAULT NULL,
  `value`            text,
  `description`      varchar(255)    DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_category_config_name` (`category`, `config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. lf_rep_targets
--    Annual quota and weekly KPI targets per rep per fiscal year.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_rep_targets` (
  `id`                    char(36)        NOT NULL,
  `name`                  varchar(255)    DEFAULT NULL,
  `date_entered`          datetime        DEFAULT NULL,
  `date_modified`         datetime        DEFAULT NULL,
  `modified_user_id`      char(36)        DEFAULT NULL,
  `created_by`            char(36)        DEFAULT NULL,
  `deleted`               tinyint(1)      DEFAULT '0',
  `assigned_user_id`      char(36)        NOT NULL,
  `fiscal_year`           int             DEFAULT NULL,
  `annual_quota`          decimal(26,6)   DEFAULT NULL,
  `weekly_new_pipeline`   decimal(26,6)   DEFAULT NULL,
  `weekly_progression`    decimal(26,6)   DEFAULT NULL,
  `weekly_closed`         decimal(26,6)   DEFAULT NULL,
  `is_active`             tinyint(1)      DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_rep_targets_user_year` (`assigned_user_id`, `fiscal_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. lf_weekly_report
--    End-of-week report record linking back to the plan.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_weekly_report` (
  `id`                  char(36)        NOT NULL,
  `name`                varchar(255)    DEFAULT NULL,
  `date_entered`        datetime        DEFAULT NULL,
  `date_modified`       datetime        DEFAULT NULL,
  `modified_user_id`    char(36)        DEFAULT NULL,
  `created_by`          char(36)        DEFAULT NULL,
  `deleted`             tinyint(1)      DEFAULT '0',
  `lf_weekly_plan_id`   char(36)        DEFAULT NULL,
  `assigned_user_id`    char(36)        NOT NULL,
  `week_start_date`     date            DEFAULT NULL,
  `status`              varchar(255)    DEFAULT 'in_progress',
  `submitted_date`      datetime        DEFAULT NULL,
  `reviewed_by`         char(36)        DEFAULT NULL,
  `reviewed_date`       datetime        DEFAULT NULL,
  `notes`               text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_week` (`assigned_user_id`, `week_start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. lf_report_snapshots
--    Per-opportunity snapshot captured when a weekly report is generated.
--    Used as fallback baseline for the Progression KPI when the cron-based
--    opportunity_weekly_snapshot has no data for that week.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lf_report_snapshots` (
  `id`                    char(36)        NOT NULL,
  `name`                  varchar(255)    DEFAULT NULL,
  `date_entered`          datetime        DEFAULT NULL,
  `date_modified`         datetime        DEFAULT NULL,
  `modified_user_id`      char(36)        DEFAULT NULL,
  `created_by`            char(36)        DEFAULT NULL,
  `deleted`               tinyint(1)      DEFAULT '0',
  `lf_weekly_report_id`   char(36)        NOT NULL,
  `opportunity_id`        char(36)        NOT NULL,
  `account_name`          varchar(255)    DEFAULT NULL,
  `opportunity_name`      varchar(255)    DEFAULT NULL,
  `amount_at_snapshot`    decimal(26,6)   DEFAULT NULL,
  `stage_at_week_start`   varchar(100)    DEFAULT NULL,
  `stage_at_week_end`     varchar(100)    DEFAULT NULL,
  `probability_at_start`  int             DEFAULT NULL,
  `probability_at_end`    int             DEFAULT NULL,
  `movement`              varchar(255)    DEFAULT NULL,
  `was_planned`           tinyint(1)      DEFAULT '0',
  `plan_category`         varchar(50)     DEFAULT NULL,
  `result_description`    text,
  `profit_at_snapshot`    decimal(26,6)   DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lf_weekly_report_id` (`lf_weekly_report_id`),
  KEY `idx_opportunity_id`      (`opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. opportunity_weekly_snapshot
--    Cron-generated snapshot of every open opportunity at end of each week.
--    Used as the primary baseline for the Progression KPI in rep reports.
--    The cron job runs every Sunday; if it has no data for a given week,
--    view.rep_report.php falls back to lf_report_snapshots.stage_at_week_start.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `opportunity_weekly_snapshot` (
  `id`               char(36)                            NOT NULL,
  `opportunity_id`   char(36)                            NOT NULL,
  `assigned_user_id` char(36)                            DEFAULT NULL,
  `account_id`       char(36)                            DEFAULT NULL,
  `week_end_at`      datetime                            NOT NULL,
  `stage_name`       varchar(100)                        NOT NULL,
  `stage_pct`        int                                 NOT NULL DEFAULT '0',
  `revenue`          decimal(26,6)                       DEFAULT NULL,
  `profit`           decimal(26,6)                       DEFAULT NULL,
  `open_at`          datetime                            DEFAULT NULL,
  `closed_status`    enum('OPEN','WON','LOST')           DEFAULT 'OPEN',
  `closed_at`        datetime                            DEFAULT NULL,
  `date_entered`     datetime                            DEFAULT NULL,
  `date_modified`    datetime                            DEFAULT NULL,
  `deleted`          tinyint(1)                          DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_opp_week`      (`opportunity_id`, `week_end_at`),
  KEY `idx_week_end`             (`week_end_at`),
  KEY `idx_assigned_week`        (`assigned_user_id`, `week_end_at`),
  KEY `idx_account_week`         (`account_id`, `week_end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
