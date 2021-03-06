<?php
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Class CRM_Case_BAO_CaseTypeTest
 */
class CRM_Case_BAO_CaseTypeTest extends CiviUnitTestCase {

  /**
   * Provide a series of test-scenarios. Each scenario includes a case-type defintion expressed as
   * JSON and equivalent XML.
   *
   * @return array
   */
  function definitionProvider() {
    $fixtures['empty-defn'] = array(
      'json' => json_encode(array()),
      'xml' => '<?xml version="1.0" encoding="iso-8859-1" ?>
<CaseType>
  <name>Housing Support</name>
</CaseType>
      ',
    );

    $fixtures['empty-lists'] = array(
      'json' => json_encode(array(
        'activitySets' => array(),
        'activityTypes' => array(),
        'caseRoles' => array(),
      )),
      'xml' => '<?xml version="1.0" encoding="iso-8859-1" ?>
<CaseType>
  <name>Housing Support</name>
  <ActivityTypes></ActivityTypes>
  <ActivitySets></ActivitySets>
  <CaseRoles></CaseRoles>
</CaseType>
      ',
    );

    $fixtures['one-item-in-each'] = array(
      'json' => json_encode(array(
        'activityTypes' => array(
          array('name' => 'First act'),
        ),
        'activitySets' => array(
          array(
            'name' => 'set1',
            'label' => 'Label 1',
            'timeline' => 1,
            'activityTypes' => array(
              array('name' => 'Open Case', 'status' => 'Completed'),
            ),
          ),
        ),
        'caseRoles' => array(
          array('name' => 'First role', 'creator' => 1, 'manager' => 1),
        ),
      )),
      'xml' => '<?xml version="1.0" encoding="iso-8859-1" ?>
<CaseType>
  <name>Housing Support</name>
  <ActivityTypes>
    <ActivityType>
      <name>First act</name>
    </ActivityType>
  </ActivityTypes>
  <ActivitySets>
    <ActivitySet>
      <name>set1</name>
      <label>Label 1</label>
      <timeline>true</timeline>
      <ActivityTypes>
        <ActivityType>
          <name>Open Case</name>
          <status>Completed</status>
        </ActivityType>
      </ActivityTypes>
    </ActivitySet>
  </ActivitySets>
  <CaseRoles>
    <RelationshipType>
      <name>First role</name>
      <creator>1</creator>
      <manager>1</manager>
    </RelationshipType>
  </CaseRoles>
</CaseType>
      ',
    );

    $fixtures['two-items-in-each'] = array(
      'json' => json_encode(array(
        'activityTypes' => array(
          array('name' => 'First act'),
          array('name' => 'Second act'),
        ),
        'activitySets' => array(
          array(
            'name' => 'set1',
            'label' => 'Label 1',
            'timeline' => 1,
            'activityTypes' => array(
              array('name' => 'Open Case', 'status' => 'Completed'),
              array(
                'name' => 'Meeting',
                'reference_activity' => 'Open Case',
                'reference_offset' => 1,
                'reference_select' => 'newest',
              ),
            ),
          ),
          array(
            'name' => 'set2',
            'label' => 'Label 2',
            'sequence' => 1,
            'activityTypes' => array(
              array('name' => 'First act'),
              array('name' => 'Second act'),
            ),
          ),
        ),
        'caseRoles' => array(
          array('name' => 'First role', 'creator' => 1, 'manager' => 1),
          array('name' => 'Second role'),
        ),
      )),
      'xml' => '<?xml version="1.0" encoding="iso-8859-1" ?>
<CaseType>
  <name>Housing Support</name>
  <ActivityTypes>
    <ActivityType>
      <name>First act</name>
    </ActivityType>
    <ActivityType>
      <name>Second act</name>
    </ActivityType>
  </ActivityTypes>
  <ActivitySets>
    <ActivitySet>
      <name>set1</name>
      <label>Label 1</label>
      <timeline>true</timeline>
      <ActivityTypes>
        <ActivityType>
          <name>Open Case</name>
          <status>Completed</status>
        </ActivityType>
        <ActivityType>
          <name>Meeting</name>
          <reference_activity>Open Case</reference_activity>
          <reference_offset>1</reference_offset>
          <reference_select>newest</reference_select>
        </ActivityType>
      </ActivityTypes>
    </ActivitySet>
    <ActivitySet>
      <name>set2</name>
      <label>Label 2</label>
      <sequence>true</sequence>
      <ActivityTypes>
        <ActivityType>
          <name>First act</name>
        </ActivityType>
        <ActivityType>
          <name>Second act</name>
        </ActivityType>
      </ActivityTypes>
    </ActivitySet>
  </ActivitySets>
  <CaseRoles>
    <RelationshipType>
      <name>First role</name>
      <creator>1</creator>
      <manager>1</manager>
    </RelationshipType>
    <RelationshipType>
      <name>Second role</name>
    </RelationshipType>
  </CaseRoles>
</CaseType>
      ',
    );

    $cases = array();
    foreach (array(
               'empty-defn',
               'empty-lists',
               'one-item-in-each',
               'two-items-in-each',
             ) as $key) {
      $cases[] = array($key, $fixtures[$key]['json'], $fixtures[$key]['xml']);
    }
    return $cases;
  }

  /**
   * @param string $fixtureName
   * @param string $expectedJson
   * @param string $inputXml
   * @dataProvider definitionProvider
   */
  function testConvertXmlToDefinition($fixtureName, $expectedJson, $inputXml) {
    $xml = simplexml_load_string($inputXml);
    $expectedDefinition = json_decode($expectedJson, TRUE);
    $actualDefinition = CRM_Case_BAO_CaseType::convertXmlToDefinition($xml);
    $this->assertEquals($expectedDefinition, $actualDefinition);
  }

  /**
   * @param string $fixtureName
   * @param string $inputJson
   * @param string $expectedXml
   * @dataProvider definitionProvider
   */
  function testConvertDefinitionToXml($fixtureName, $inputJson, $expectedXml) {
    $inputDefinition = json_decode($inputJson, TRUE);
    $actualXml = CRM_Case_BAO_CaseType::convertDefinitionToXML('Housing Support', $inputDefinition);
    $this->assertEquals($this->normalizeXml($expectedXml), $this->normalizeXml($actualXml));
  }

  /**
   * @param string $fixtureName
   * @param string $ignore
   * @param string $inputXml
   * @dataProvider definitionProvider
   */
  function testRoundtrip_XmlToJsonToXml($fixtureName, $ignore, $inputXml) {
    $tempDefinition = CRM_Case_BAO_CaseType::convertXmlToDefinition(simplexml_load_string($inputXml));
    $actualXml = CRM_Case_BAO_CaseType::convertDefinitionToXML('Housing Support', $tempDefinition);
    $this->assertEquals($this->normalizeXml($inputXml), $this->normalizeXml($actualXml));
  }

  /**
   * @param string $fixtureName
   * @param string $inputJson
   * @param string $ignore
   * @dataProvider definitionProvider
   */
  function testRoundtrip_JsonToXmlToJson($fixtureName, $inputJson, $ignore) {
    $tempXml = CRM_Case_BAO_CaseType::convertDefinitionToXML('Housing Support', json_decode($inputJson, TRUE));
    $actualDefinition = CRM_Case_BAO_CaseType::convertXmlToDefinition(simplexml_load_string($tempXml));
    $expectedDefinition = json_decode($inputJson, TRUE);
    $this->assertEquals($expectedDefinition, $actualDefinition);
  }

  /**
   * Normalize the whitespace in an XML document
   *
   * @param string $xml
   * @return string
   */
  function normalizeXml($xml) {
    return trim(
      preg_replace(":\n*<:", "\n<", // tags on new lines
        preg_replace("/\n[\n ]+/", "\n", // no leading whitespace
          $xml
        )
      )
    );
  }
}