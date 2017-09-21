<?php

namespace Drupal\unical\Plugin\resource\search;

use Drupal\restful\Plugin\resource\ResourceInterface;
use Drupal\restful_search_api\Plugin\Resource\ResourceSearchBase;

/**
 * Class Search
 * @package Drupal\unical\Plugin\resource\search
 *
 * @Resource(
 *   name = "eventsearch:1.0",
 *   resource = "eventsearch",
 *   label = "Event Search",
 *   description = "Search events",
 *   dataProvider = {
 *     "searchIndex": "events_index"
 *   },
 *   renderCache = {
 *     "render": FALSE,
 *   },
 *   authenticationTypes = TRUE,
 *   authenticationOptional = TRUE,
 *   formatter = "json",
 *   majorVersion = 1,
 *   minorVersion = 0,
 * )
 */
class Search extends ResourceSearchBase implements ResourceInterface {

  /**
   * Overrides Resource::publicFields().
   */
  public function publicFields() {
    $public_fields['id'] = array(
      'property' => 'search_api_id',
      'process_callbacks' => array(
        'intVal',
      ),
    );
    $public_fields['version_id'] = array(
      'property' => 'vid',
      'process_callbacks' => array(
        'intVal',
      ),
    );
    $public_fields['relevance'] = array(
      'property' => 'search_api_relevance',
    );
    $public_fields['label'] = array(
      'property' => 'title',
    );
    $public_fields['body_trimmed'] = array(
      'property' => 'body',
      'sub_property' => 'value',
      'process_callbacks' => array(
        array($this, 'processBody'),
      ),
    );
    $public_fields['image'] = array(
      'property' => 'field_image',
      'image_styles' => array('thumbnail', 'medium', 'large'),
      'process_callbacks' => array(
        array($this, 'processImage'),
      ),
    );
    $public_fields['date'] = array(
      'property' => 'field_date',
      'process_callbacks' => array(
        array($this, 'processDate'),
      ),
    );
    $public_fields['timezone'] = array(
      'property' => 'field_timezone',
      'process_callbacks' => array(
        array($this, 'processArrayValue'),
      ),
    );
    $public_fields['uri'] = array(
      'property' => 'nid',
      'process_callbacks' => array(
        array($this, 'processUri'),
      ),
    );
    $public_fields['address'] = array(
      'property' => 'field_address',
      'process_callbacks' => array(
        array($this, 'processAddress'),
      ),
    );
    $public_fields['exclude_from_main_calendar'] = array(
      'property' => 'field_exclude_from_main_calendar',
      'process_callbacks' => array(
        array($this, 'processArrayValue'),
      ),
    );
      return $public_fields;
  }
  /**
   * Process the Body.
   */
  public function processBody($data) {
    return text_summary(drupal_html_to_text($data['und'][0]['value'], array('<strong>', '<em>')), NULL, 250);
  }
  /**
   * Process the Image.
   */
  public function processImage($data) {
    $data = $data['und'][0];
    $data['image_styles']['medium'] = image_style_url('medium', $data['uri']);
    $data['image_styles']['large'] = image_style_url('large', $data['uri']);
    return $data;
  }
  /**
   * Process the Date.
   */
  public function processDate($data) {
    $data = $data['und'];
    // Get timestamps based on date as stored in DB (no timeszone conversion).
    $unix_start = strtotime($data[0]['value']);
    $unix_end = strtotime($data[0]['value2']);
    // Get UNIX timestamp.
    $data[0]['start_unix'] = $unix_start;
    $data[0]['end_unix'] = $unix_end;
    // Specific formats for event list display.
    $data[0]['start_month'] = date('M', $unix_start);
    $data[0]['start_day'] = date('j', $unix_start);
    $data[0]['start_time'] = date('g:i A', $unix_start);
    $data[0]['end_time'] = date('g:i A', $unix_end);
    // Specific formats for "Add to Calendar"(see https://addthisevent.com)
    $data[0]['start_addto'] = date('m/d/Y g:i A', $unix_start);
    $data[0]['end_addto'] = date('m/d/Y g:i A', $unix_end);
    // Return.
    return $data;
  }
  /**
   * Removes value from array.
   */
  public function processArrayValue($data) {
    $data = $data['und'][0]['value'];
    // Return.
    return $data;
  }
  /**
   * Process the URI.
   */
  public function processUri($id) {
    $path = drupal_get_path_alias('node/' . $id);
    // If the path contains more than one part, get the last part.
    if (strpos($path, '/') !== FALSE) {
      $parts = explode('/', $path);
      $path = $parts[sizeof($parts) - 1];
    }
    return $path;
  }
  /**
   * Process the Address.
   */
  public function processAddress($data) {
    // Unserialize data so we have formatted address, longitude etc.
    $data['data'] = unserialize($data['und'][0]['data']);
    return $data;
  }

}
