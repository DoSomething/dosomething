<?php

class ReportbackItemTransformer extends ReportbackTransformer {

  /**
   * @param array $parameters Any parameters obtained from query string.
   * @return array
   */
  public function index($parameters) {
    $filters = [
      'nid' => dosomething_helpers_format_data($parameters['campaigns']),
      'status' => dosomething_helpers_format_data($parameters['status']),
      'count' => (int) $parameters['count'] ?: 25,
      'page' => (int) $parameters['page'],
    ];

    $filters['offset'] = $this->setOffset($filters['page'], $filters['count']);

    // @TODO: Logic update!
    // Not ideal that this is NULL instead of FALSE but due to how logic happens in original query function. It should be updated!
    // Logic currently checks for isset() instead of just boolean, so won't change until endpoints switched.
    $filters['random'] = $parameters['random'] === 'true' ? TRUE : NULL;
    $filters['load_user_data'] = $parameters['load_user_data'] === 'true' ? TRUE : NULL;

    try {
      $reportbackItems = ReportbackItem::find($filters);
      $reportbackItems = services_resource_build_index_list($reportbackItems, 'reportback-items', 'id');
      $total = $this->getTotalCount($filters);
    }
    catch (Exception $error) {
      return [
        'error' => [
          'message' => $error->getMessage(),
        ],
      ];
    }

    return [
      'pagination' => $this->paginate($total, $filters, 'reportback-items'),
      'retrieved_count' => count($reportbackItems),
      'data' => $this->transformCollection($reportbackItems),
    ];
  }


  /**
   * Display the specified resource.
   *
   * @param string $id Resource id.
   * @return array
   */
  public function show($id) {
    try {
      $reportbackItem = ReportbackItem::get($id);
      $reportbackItem = services_resource_build_index_list($reportbackItem, 'reportback-items', 'id');
    }
    catch (Exception $error) {
      return [
        'error' => [
          'message' => $error->getMessage(),
        ],
      ];
    }

    return array(
      'data' => $this->transform(array_pop($reportbackItem)),
    );
  }


  /**
   * Transform data and build out response.
   *
   * @param object $item Single object of retrieved data.
   * @return array
   */
  protected function transform($item) {
    $data = [];

    $data += $this->transformReportbackItem($item);

    $data['reportback'] = $this->transformReportback((object) $item->reportback);

    $data['campaign'] = $this->transformCampaign((object) $item->campaign);

    $data['user'] = $this->transformUser((object) $item->user);

    return $data;
  }

}
