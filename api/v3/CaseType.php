<?php
/*
  +--------------------------------------------------------------------+
  | CiviCRM version 4.5                                                |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2014                                |
  +--------------------------------------------------------------------+
  | This file is a part of CiviCRM.                                    |
  |                                                                    |
  | CiviCRM is free software; you can copy, modify, and distribute it  |
  | under the terms of the GNU Affero General Public License           |
  | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
  |                                                                    |
  | CiviCRM is distributed in the hope that it will be useful, but     |
  | WITHOUT ANY WARRANTY; without even the implied warranty of         |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
  | See the GNU Affero General Public License for more details.        |
  |                                                                    |
  | You should have received a copy of the GNU Affero General Public   |
  | License and the CiviCRM Licensing Exception along                  |
  | with this program; if not, contact CiviCRM LLC                     |
  | at info[AT]civicrm[DOT]org. If you have questions about the        |
  | GNU Affero General Public License or the licensing of CiviCRM,     |
  | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
  +--------------------------------------------------------------------+
*/

/**
 * File for the CiviCRM APIv3 Case functions
 * Developed by woolman.org
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Case
 * @copyright CiviCRM LLC (c) 2004-2014
 *
 */

/**
 * Function to create or update case type
 *
 * @param  array $params   input parameters
 *
 * Allowed @params array keys are:
 * {@getfields case_type_create}
 *
 * @throws API_Exception
 * @return array API result array
 *
 * @static void
 * @access public
 */
function civicrm_api3_case_type_create($params) {
  civicrm_api3_verify_mandatory($params, _civicrm_api3_get_DAO(__FUNCTION__));

  if (!array_key_exists('is_active', $params) && empty($params['id'])) {
    $params['is_active'] = TRUE;
  }
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'CaseType');

}

/**
 * Function to retrieve case types
 *
 * @param $params
 *
 * @return array $caseTypes case types keyed by id
 * @access public
 */
function civicrm_api3_case_type_get($params) {
  civicrm_api3_verify_mandatory($params);
  $caseTypes = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

  // format case type, to fetch xml definition
  return _civicrm_api3_case_type_get_formatResult($caseTypes);
}

/**
 * Function to format definition
 *
 * @param $caseTypes
 * @return mixed
 */
function _civicrm_api3_case_type_get_formatResult(&$result) {
  foreach ($result['values'] as $key => $caseType) {
    $xml = CRM_Case_XMLRepository::singleton()->retrieve($caseType['name']);
    if ($xml) {
      $result['values'][$key]['definition'] = CRM_Case_BAO_CaseType::convertXmlToDefinition($xml);
    } else {
      $result['values'][$key]['definition'] = array();
    }
  }
  return $result;
}

/**
 * Function to delete case type
 *
 * @param array $params array including id of case_type to delete

 * @return array API result array
 *
 * @access public
 *
 */
function civicrm_api3_case_type_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
