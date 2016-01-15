<?php

class Campaign {

  protected $node;
  protected $variables;

  public $id;
  public $title;
  public $display;
  public $tagline;
  public $created_at;
  public $updated_at;
  public $status;
  public $type;
  public $language;
  public $translations;
  public $campaign_runs;
  public $time_commitment;
  public $cover_image;
  public $scholarship;
  public $staff_pick;
  public $facts;
  public $solutions;
  public $causes;
  public $action_types;
  public $issue;
  public $tags;
  public $timing;
  public $reportback_info;
  public $services;
  public $mobile_app;

  /**
   * Convenience method to retrieve a single campaign from supplied id.
   *
   * @param  string|array  $ids  Single id or array of ids of Campaigns to retrieve.
   * @param  string        $display
   * @return static
   * @throws Exception
   */
  public static function get($ids, $display = 'teaser') {
    $campaigns = [];

    if (!is_array($ids)) {
      $ids = [$ids];
    }

    $results = node_load_multiple($ids);

    if (!$results) {
      throw new Exception('No campaign data found.');
    }

    foreach($results as $item) {
      $campaign = new static();
      $campaign->build($item, $display);

      $campaigns[] = $campaign;
    }

    return $campaigns;
  }

  /**
   * Convenience method to retrieve campaigns based on supplied filters.
   *
   * @param  array  $filters
   * - nid (string|array)
   * - type (string)
   * - staff_pick (bool)
   * - mobile_app (bool)
   * - mobile_app_date (string)
   * - term_id (string|array)
   * - count (int)
   * - random (bool)
   * - page (int)
   * @param  string  $display
   * @return array
   * @throws Exception
   */
  public static function find(array $filters = [], $display = 'teaser') {
    $campaigns = [];

    $results = dosomething_campaign_get_campaigns_query($filters);

    if (!$results) {
      throw new Exception('No campaign data found.');
    }

    $results = array_map('dosomething_helpers_extract_id', $results);
    $results = node_load_multiple($results);

    foreach($results as $item) {
      $campaign = new static;
      $campaign->build($item, $display);

      $campaigns[] = $campaign;
    }

    return $campaigns;
  }

  /**
   * Return node data for this campaign instance.
   *
   * @return mixed
   */
  public function getNodeData() {
    return $this->node;
  }

  /**
   * Return variables data for this campaign instance.
   *
   * @return mixed
   */
  public function getVariablesData() {
    return $this->variables;
  }

  /**
   * Build out the instantiated Campaign class object with supplied data.
   *
   * @param  $data
   * @param  $display
   * @throws Exception
   */
  private function build($data, $display = 'teaser') {
    $this->node = $data;
    $this->id = $data->nid;

    if ($data->type === 'campaign') {
      $this->variables = dosomething_helpers_get_variables('node', $this->id);
      $this->title = $data->title;
      $this->display = $display;

      if ($display === 'full') {
        $this->tagline = $this->getTagline();
        $this->created_at = $data->created;
        $this->updated_at = $data->changed;
        $this->status = $this->getStatus();
        $this->type = $this->getType();
        $this->time_commitment = $this->getTimeCommitment();

        $this->cover_image = [
          'default' => $this->getCoverImage(),
          'alternate' => $this->getCoverImageAlt(),
        ];

        $this->scholarship = $this->getScholarship();
        $this->staff_pick = $this->getStaffPickStatus();

        $fact_data = $this->getFactData();
        $this->facts = [
          'problem' => $fact_data['fact_problem'],
          'solution' => $fact_data['fact_solution'],
          'sources' => $fact_data['sources'],
        ];

        $solution_data = $this->getSolutionData();
        $this->solutions = $solution_data;

        $this->causes = $this->getCauses();

        $this->action_types = $this->getActionTypes();

        $this->issue = $this->getIssue();
        $this->tags = $this->getTags();

        $timing = $this->getTiming();
        $this->timing = $timing;

        $this->services = $this->getServices();

        $this->mobile_app = $this->getMobileAppDate();
      }

      $this->reportback_info = $this->getReportbackInfo();
    }
  }

  /**
   * Get both primary and secondary Action Types for campaign if available.
   *
   * @return array
   */
  protected function getActionTypes() {
    // @TODO: Potentially (or very likely can) combine this with getCauses() to DRY up code.
    $data = [];
    $data['primary'] = NULL;
    $data['secondary'] = NULL;

    $primary_action_type_id = dosomething_helpers_extract_field_data($this->node->field_primary_action_type);
    $secondary_action_type_ids = dosomething_helpers_extract_field_data($this->node->field_action_type);

    if ($primary_action_type_id) {
      $data['primary'] = $this->getTaxonomyTerm($primary_action_type_id);
    }

    if ($secondary_action_type_ids) {
      $secondary_action_types = [];

      foreach((array) $secondary_action_type_ids as $tid) {
        $secondary_action_types[] = $this->getTaxonomyTerm($tid);
      }

      $data['secondary'] = $secondary_action_types;
    }

    return $data;
  }

  /**
   * Get both primary and secondary Causes for campaign if available.
   *
   * @return array
   */
  protected function getCauses() {
    // @TODO: Potentially combine this with getActionTypes() to DRY up code.
    $data = [];
    $data['primary'] = NULL;
    $data['secondary'] = NULL;

    $primary_cause_id = dosomething_helpers_extract_field_data($this->node->field_primary_cause);
    $secondary_cause_ids = dosomething_helpers_extract_field_data($this->node->field_cause);

    if ($primary_cause_id) {
      $data['primary'] = $this->getTaxonomyTerm($primary_cause_id);
    }

    if ($secondary_cause_ids) {
      if (is_array($secondary_cause_ids)) {
        $secondary_causes = [];

        foreach($secondary_cause_ids as $tid) {
          $secondary_causes[] = $this->getTaxonomyTerm($tid);
        }
      }
      else {
        $secondary_causes[] = $this->getTaxonomyTerm($secondary_cause_ids);
      }

      $data['secondary'] = $secondary_causes;
    }

    return $data;
  }

  /**
   * Get the cover image data for campaign if available.
   *
   * @param  string|null  $id Image node id.
   * @return array|null
   */
  protected function getCoverImage($id = NULL) {
    if (is_null($id)) {
      $id = dosomething_helpers_extract_field_data($this->node->field_image_campaign_cover);
    }

    // @TODO: This could potentially be turned into an Image class, and load the data by including and then instantiating the class $image = new Image($id);
    // This would help cleanup some of the code, and keep things DRYer.
    if ($id) {
      $image = node_load($id);
    }
    else {
      return NULL;
    }

    $data = [];

    $data['id'] = $image->nid;
    $data['type'] = $image->type;
    $data['title'] = $image->title;
    $data['created_at'] = $image->created;
    $data['updated_at'] = $image->changed;
    $data['dark_background'] = (bool) dosomething_helpers_extract_field_data($image->field_dark_image);

    // @TODO: consider reworking following function to return data instead of assigning it by reference to variable. Kind of confusing.
    dosomething_campaign_load_image_url($data['sizes'], $image);
    if ($data['sizes']['landscape']) {
      $data['sizes']['landscape']['uri'] = dosomething_image_get_themed_image_url($image->nid, 'landscape', '1440x810');
    }

    if ($data['sizes']['square']) {
      $data['sizes']['square']['uri'] = dosomething_image_get_themed_image_url($image->nid, 'square', '300x300');
    }

    return $data;
  }

  /**
   * Get alternative cover image for campaign if available.
   *
   * @return array|null
   */
  protected function getCoverImageAlt() {
    $image_id = $this->variables['alt_image_campaign_cover_nid'];

    if ($image_id) {
      return $this->getCoverImage($image_id);
    }
    else {
      return NULL;
    }
  }

  /**
   * Get Facts data for campaign if available; collects both fact problem
   * and fact solution as well as the sources for both.
   *
   * @return array|null
   */
  protected function getFactData() {
    $data = [];
    $data['fact_problem'] = NULL;
    $data['fact_solution'] = NULL;
    $data['sources'] = NULL;

    $fact_fields = ['field_fact_problem', 'field_fact_solution'];
    $fact_vars = dosomething_fact_get_mutiple_fact_field_vars($this->node, $fact_fields);

    if (!empty($fact_vars)) {
      foreach ($fact_vars['facts'] as $index => $fact_data) {
        $index = str_replace('field_', '', $index);

        $data[$index]['fact'] = dosomething_helpers_isset($fact_data['fact']);
        $data[$index]['id'] = dosomething_helpers_isset($fact_data['nid']);
        $data[$index]['footnote'] = (int) dosomething_helpers_isset($fact_data['footnotes']);
        $data[$index]['source'] = dosomething_helpers_isset($fact_data['sources']);

      }

      $sources = dosomething_helpers_isset($fact_vars['sources']);
      foreach ($sources as $index => $source) {
        $data['sources'][$index]['formatted'] = $source;
      }

      return $data;
    }

    return NULL;

  }

  /**
   * Get the Issue for campaign if available.
   *
   * @return array|null
   */
  protected function getIssue() {
    // @TODO: Need to find out if this is allowed to be a field with multiple values...?
    $issue_id = dosomething_helpers_extract_field_data($this->node->field_issue);

    if ($issue_id) {
      $issue = $this->getTaxonomyTerm($issue_id);

      return $issue;
    }

    return NULL;
  }

  /**
   * Get MailChimp data.
   *
   * @return array
   */
  protected function getMailChimpData() {
    return [
      'grouping_id' => dosomething_helpers_isset($this->variables, 'mailchimp_grouping_id'),
      'group_name' => dosomething_helpers_isset($this->variables, 'mailchimp_group_name'),
    ];
  }

  /**
   * Get Mobile Commons data.
   *
   * @return array
   */
  protected function getMobileCommonsData() {
    return [
      'opt_in_path_id' => dosomething_helpers_isset($this->variables, 'mobilecommons_opt_in_path'),
      'friends_opt_in_path_id' => dosomething_helpers_isset($this->variables, 'mobilecommons_friends_opt_in_path'),
    ];
  }

   /**
   * Get the start and end dates for when campaign will be displayed on the mobile app if available.
   * Dates formatted as ISO-8601 datetime.
   *
   * @return array
   */

    protected function getMobileAppDate() {
      $timing = [];
      $timing['dates'] = (dosomething_helpers_extract_field_data($this->node->field_mobile_app_date));

      if (isset($timing['dates'])) {
        foreach ($timing['dates'] as $key => $date) {
          $timing['dates'][$key] = dosomething_helpers_convert_date($date);
        }
      }

      return $timing;
  }

  /**
   * Get Reportback content info used in the campaign.
   *
   * @return array
   */
  protected function getReportbackInfo() {
    $data = [];
    $data['copy'] = NULL;
    $data['confirmation_message'] = NULL;
    $data['noun'] = NULL;
    $data['verb'] = NULL;

    $copy = dosomething_helpers_extract_field_data($this->node->field_reportback_copy);
    $confirmation_message = dosomething_helpers_extract_field_data($this->node->field_reportback_confirm_msg);
    $noun = dosomething_helpers_extract_field_data($this->node->field_reportback_noun);
    $verb = dosomething_helpers_extract_field_data($this->node->field_reportback_verb);

    if ($copy) {
      $data['copy'] = $copy;
    }

    if ($confirmation_message) {
      $data['confirmation_message'] = $confirmation_message;
    }

    if ($noun) {
      $data['noun'] = $noun;
    }

    if ($verb) {
      $data['verb'] = $verb;
    }

    return $data;
  }

  /**
   * Collect data for third party services.
   *
   * @return array
   */
  protected function getServices() {
    return [
      'mobile_commons' => $this->getMobileCommonsData(),
      'mailchimp' => $this->getMailChimpData(),
    ];
  }

  /**
   * Get the Scholarship amount for campaign if available.
   *
   * @return array|null
   */
  protected function getScholarship() {
    return dosomething_helpers_extract_field_data($this->node->field_scholarship_amount);
  }

  /**
   * Get the Solutions data for campaign if available; collects both the main solution copy
   * and the solution support copy.
   *
   * @return array
   */
  protected function getSolutionData() {
    $data['copy'] = dosomething_helpers_extract_field_data($this->node->field_solution_copy);
    $data['support_copy'] = dosomething_helpers_extract_field_data($this->node->field_solution_support);

    return $data;
  }

  /**
   * Get status whether this campaign is a Staff Pick or not.
   *
   * @return bool
   */
  protected function getStaffPickStatus() {
    return (bool) dosomething_helpers_extract_field_data($this->node->field_staff_pick);
  }

  /**
   * Get Status of campaign.
   *
   * @return string|null
   */
  protected function getStatus() {
    return dosomething_helpers_extract_field_data($this->node->field_campaign_status);
  }

  /**
   * Get Tags assigned to campaign if available.
   *
   * @return array|null
   */
  protected function getTags() {
    $data = [];

    $tag_ids = dosomething_helpers_extract_field_data($this->node->field_tags);

    if ($tag_ids) {
      foreach ((array) $tag_ids as $id) {
        $data[] = $this->getTaxonomyTerm($id);
      }

      return $data;
    }

    return NULL;
  }

  /**
   * Get taxonomy term node data from provided id.
   *
   * @param  $id
   * @return array
   */
  protected function getTaxonomyTerm($id) {
    $data = [];

    $taxonomy = taxonomy_term_load($id);

    $data['id'] = $taxonomy->tid;
    $data['name'] = strtolower($taxonomy->name);

    return $data;
  }

  /**
   * Get the Tagline for campaign.
   *
   * @return array|null
   */
  protected function getTagline() {
    return dosomething_helpers_extract_field_data($this->node->field_call_to_action);
  }

  /**
   * Get the specified Time Commitment for campaign.
   *
   * @return float
   */
  protected function getTimeCommitment() {
    // @TODO: I've renamed "active_hours" to "time_commitment" because it sounds more straightforward; but appreciate feedback.
    return (float) dosomething_helpers_extract_field_data($this->node->field_active_hours);
  }

  /**
   * Get the timing for high and low seasons for campaign if available.
   * Dates formatted as ISO-8601 datetime.
   *
   * @return array
   */
  protected function getTiming() {
    $timing = [];
    $timing['high_season'] = dosomething_helpers_extract_field_data($this->node->field_high_season);
    $timing['low_season'] = dosomething_helpers_extract_field_data($this->node->field_low_season);

    foreach ($timing as $season => $dates) {
      if ($timing[$season]) {
        foreach ($timing[$season] as $key => $date) {
          $timing[$season][$key] = dosomething_helpers_convert_date($date);
        }
      }
    }

    return $timing;
  }

  /**
   * Get Type of campaign.
   *
   * @return string|null
   */
  protected function getType() {
    return dosomething_helpers_extract_field_data($this->node->field_campaign_type);
  }

}
