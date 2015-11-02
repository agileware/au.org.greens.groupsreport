<?php

require_once 'groupsreport.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function groupsreport_civicrm_config(&$config) {
  _groupsreport_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function groupsreport_civicrm_xmlMenu(&$files) {
  _groupsreport_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function groupsreport_civicrm_install() {
  _groupsreport_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function groupsreport_civicrm_uninstall() {
  _groupsreport_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function groupsreport_civicrm_enable() {
  _groupsreport_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function groupsreport_civicrm_disable() {
  _groupsreport_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function groupsreport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _groupsreport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function groupsreport_civicrm_managed(&$entities) {
  _groupsreport_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function groupsreport_civicrm_caseTypes(&$caseTypes) {
  _groupsreport_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function groupsreport_civicrm_angularModules(&$angularModules) {
_groupsreport_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function groupsreport_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _groupsreport_civix_civicrm_alterSettingsFolders($metaDataFolders);

}

/**
 * SQL to find disabled custom data fields
 * @return string $sql
 */
function groupsReport_custom_field_disabled_sql() {
  $sql = "SELECT cf.label as custom_field, 
    CONCAT(cg.table_name, '.', cf.column_name) as test_field, cg.title as custom_group
    FROM civicrm_custom_field cf
    INNER JOIN civicrm_custom_group cg ON cg.id = cf.custom_group_id
    WHERE cf.is_active = 0";
  return $sql;
}

/**
 * SQL to find disabled custom data groups
 * @return string $sql
 */
function groupsReport_custom_group_disabled_sql() {
  $sql = "SELECT title as custom_group, table_name
    FROM civicrm_custom_group
    WHERE is_active = 0";
  return $sql;
}

/**
 * Find groups that rely on problematic groups
 * @param array $groupIds array of problematic groups
 * @return array $groups array of groups
 */
function groupsReport_problematic_groups_where($groupIds) {
  $groups = array();
  foreach ($groupIds as $groupId) {
    $key = "`civicrm_group_contact-$groupId`";
    $key2 = "`civicrm_group_contact_$groupId`";
    $sql = "SELECT g.id, g.title
      FROM civicrm_group g
      INNER JOIN civicrm_saved_search css
      ON css.id = g.saved_search_id
      WHERE (css.where_clause like '$key'
       OR css.where_clause like '$key2')
      AND g.is_active = 1";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $groups[] = array('id' => $dao->id,
        'title' => $dao->title,
        'reason' => ts('This group uses ' . $groupId . 'as part of the where clause'),
        );
    }
  }
  return $groups;
}

/**
 * Find all the problematic smart groups
 * that use disabled custom data
 * @return array $groups
 */
function groupsReport_problematic_groups_search() {
  $problemGroups = array();
  $fieldSql = groupsReport_custom_field_disabled_sql();
  $fieldDao = CRM_Core_DAO::executeQuery($fieldSql);
  while ($fieldDao->fetch()) {
    $check_sql = "SELECT g.id, g.title
      FROM civicrm_group g
      INNER JOIN civicrm_saved_search css
      ON css.id = g.saved_search_id
      WHERE css.where_clause LIKE '%$fieldDao->test_field%'
      AND g.is_active = 1";
    $dao = CRM_Core_DAO::executeQuery($check_sql);
    while ($dao->fetch()) {
      $problemGroups[] = array('id' => $dao->id,
        'title' => $dao->title,
        'reason' => ts('This Group uses ' . $fieldDao->custom_field . ' as part of its criteria from custom_group ' . $fieldDao->custom_group),
      );
    }
  }
  $groupSql = groupsReport_custom_group_disabled_sql();
  $groupDao = CRM_Core_DAO::executeQuery($groupSql);
  while ($groupDao->fetch()) {
    $check_sql = "SELECT g.id, g.title
      FROM civicrm_group g
      INNER JOIN civicrm_saved_search css
      ON css.id = g.saved_search_id
      WHERE css.where_tables like '%$groupDao->table_name%'
      AND g.is_active = 1";
    $dao = CRM_Core_DAO::executeQuery($check_sql);
    while ($dao->fetch()) {
      $groupIds = array();
      foreach ($problemGroups as $group) {
        $groupIds[] = $group['id'];
        if ($group['id'] = $dao->id) {
          $group['reason'] = $group['reason'] . ts('also custom data group' . $groupDao->custom_group);
        }
      }
      if (!in_array($dao->id, $groupIds)) {
        $problemGroups[] = array('id' => $dao->id,
          'title' => $dao->title,
          'reason' => ts('this Group users ' . $groupDao->custom_group . ' as part of its criteria'),
        );
      }
    }
  }
  $groupIds = array();
  foreach ($problemGroups as $group) {
    $groupids[] = $group['id'];
  }
  $problemWhereGroups = groupsReport_problematic_groups_where($groupIds);
  $groups = array_merge($problemGroups, $problemWhereGroups);
  return $groups;
}

/**
 * Implements hook_civicrm_pageRun
 * Adds in all the problematic groups to the page
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 * @param $page  page being rendered
 */
function groupsReport_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Groupsreport_Page_GroupsReport') {
    $groups = groupsReport_problematic_groups_search();
    $page->assign('groups', $groups);
  }
}
