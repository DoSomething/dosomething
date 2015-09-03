<?php

abstract class Transformer {

  public function __construct() {
    // Load Services module to use its index_query functions in subclass methods.
    module_load_include('inc', 'services', 'services.module');
  }


  /**
   * Format a string of comma separated data items into an array, or
   * if a single item, return it without formatting.
   *
   * @param string $data Single or multiple comma separated data items.
   * @return string|array
   */
  protected function formatData($data) {
    $array = explode(',', $data);

    if (count($array) > 1) {
      return $array;
    }

    return $data;
  }


  /**
   * Get the URI for the next page in the collection of data.
   *
   * @param array $data
   *   An associative array of pagination parameters:
   *   - total: (int) Complete total number of available data items found.
   *   - per_page: (int) Total number of data items to show per page.
   *   - current_page: (int) Current page number.
   *   - total_pages: (int) Total number of pages available.
   * @param array $parameters
   * @param string $endpoint Endpoint for building URI.
   * @return null|string
   */
  protected function getNextPageUri($data, $parameters, $endpoint) {
    $uri = $this->getPathParameters($parameters, $endpoint);

    $next = $data['current_page'] + 1;

    if ($next <= $data['total_pages']) {
      return $uri . '&page=' . $next;
    }
    else {
      return NULL;
    }
  }


  /**
   * Get the URI for the previous page in the collection of data.
   *
   * @param array $data
   *   An associative array of pagination parameters:
   *   - total: (int) Complete total number of available data items found.
   *   - per_page: (int) Total number of data items to show per page.
   *   - current_page: (int) Current page number.
   *   - total_pages: (int) Total number of pages available.
   * @param array $parameters An associative array of current location parameters.
   * @param string $endpoint Endpoint for building URI.
   * @return null|string
   */
  protected function getPrevPageUri($data, $parameters, $endpoint) {
    $uri = $this->getPathParameters($parameters, $endpoint);

    $prev = $data['current_page'] - 1;

    if ($prev > 0) {
      return $uri . '&page=' . $prev;
    }
    else {
      return NULL;
    }
  }


  /**
   * Extract the path parameters passed and recreate the query string.
   *
   * @param array $parameters
   *   An associative array of query parameters and values for building URI:
   *   - nid: (array) Campaign ids.
   *   - status: (array) Reportback statuses to retrieve.
   *   - count: (int) Number of items to show per page.
   *   - page: (int) Current page number.
   *   - offset: (int) Number of items displayed from prior pages.
   *   - random: (boolean) Whether to retrieve a random assortment of data items.
   * @param string $endpoint Endpoint for building URI.
   * @return string
   */
  protected function getPathParameters($parameters, $endpoint) {
    $path = services_resource_uri(array($endpoint));
    $path .= '.json?';

    if (isset($parameters['nid'])) {
      $path .= '&campaigns=' . implode(',', (array) $parameters['nid']);
    }

    if (isset($parameters['status'])) {
      $path .= '&status=' . implode(',', (array) $parameters['status']);
    }

    if (isset($parameters['count'])) {
      $path .= '&count=' . implode(',', (array) $parameters['count']);
    }

    return $path;
  }


  /**
   * Retrieve Reportback Items by the specified id(s).
   *
   * @param string $ids Comma separated list of Reportback Item ids.
   * @return array
   * * @deprecated since version... because I said so!
   * @TODO: Remove.
   */
  protected function getReportbackItems($ids) {
    $filters = array(
      'fid' => $this->formatData($ids),
    );

    // Obtaining all Reportback items.
    $query = dosomething_reportback_get_reportback_files_query_result($filters, 'all');

    return services_resource_build_index_list($query, 'reportback-items', 'fid');
  }


  /**
   * Get metadata for pagination of response data.
   *
   * @param int $total Complete total number of items found.
   * @param array $parameters
   *   An associative array of query parameters and values:
   *   - nid: (array) Campaign ids.
   *   - status: (array) Reportback statuses to retrieve.
   *   - count: (int) Number of items to show per page.
   *   - page: (int) Current page number.
   *   - offset: (int) Number of items displayed from prior pages.
   *   - random: (boolean) Whether to retrieve a random assortment of data items.
   * @param string $endpoint
   * @return array
   */
  protected function paginate($total, $parameters, $endpoint) {
    $data = array();

    $data['total'] = $total;
    $data['per_page'] = $parameters['count'];
    $data['current_page'] = (isset($parameters['page']) && $parameters['page'] > 0) ? (int) $parameters['page'] : 1;
    $data['total_pages'] = ceil($data['total'] / $data['per_page']);

    $prevUri = $this->getPrevPageUri($data, $parameters, $endpoint);
    $nextUri = $this->getNextPageUri($data, $parameters, $endpoint);

    $data['prev_uri'] = $prevUri;
    $data['next_uri'] = $nextUri;

    return $data;
  }


  /**
   * Get the offset for requests based on specified page number and count of items.
   *
   * @param int $page Current page number.
   * @param int $count Number of items per page.
   * @return int
   */
  protected function setOffset($page, $count) {
    if ($page > 0 ) {
      $page -= 1;
    }

    return $page * $count;
  }


  /**
   * Transform data and prepare for API response.
   *
   * @param object $object Single object of retrieved data.
   */
  abstract protected function transform($object);


  /**
   * For collection of data to transform, run each item in collection
   * through a specified method.
   *
   * @param array $items Collection of item objects retrieved data.
   * @param string $method Name of method to use on each array item.
   * @return array
   */
  protected function transformCollection($items, $method = 'transform') {
    return array_map(array($this, $method), $items);
  }


  /**
   * Transform Campaign data and prepare for API response.
   *
   * @param object $data
   *   An object containing properties of Campaign data:
   *   - id: (string)
   *   - title: (string)
   *   - tagline: (string)
   *   - created_at: (string)
   *   - update_at: (string)
   *   - time_commitment: (float)
   *   - status: (string)
   *   - cover_image: (array)
   *   - staff_pick: (bool)
   *   - facts: (array)
   *   - solutions: (array)
   *   - causes: (array)
   *   - action_types: (array)
   *   - issue: (string)
   *   - tags: (array)
   *   - timing: (array)
   *   - services: (array)
   *   - reportback_info: (array)
   *   - mobile_app: (array)
   * @return array
   */
  protected function transformCampaign($data) {
    $output = array(
      'id' => isset($data->id) ? $data->id : $data->nid,
      'title' => $data->title,
    );

    // If an instance of Campaign class, then there is much
    // more information that can be obtained.
    if ($data instanceof Campaign) {

      // Show all properties for "full" display.
      if ($data->display === 'full') {
        $output['tagline'] = $data->tagline;

        $output['created_at'] = $data->created_at;

        $output['updated_at'] = $data->updated_at;

        $output['time_commitment'] = $data->time_commitment;

        // $output['type'] = $data->type; //@TODO: Should type be included? Consider there is an SMS Campaign type...

        $output['status'] = $data->status ? $data->status : "active";

        foreach ($data->cover_image as $key => $image) {
          if (!is_null($image)) {
            $output['cover_image'][$key] = $this->transformMedia($image, 'square');
          } else {
            $output['cover_image'][$key] = NULL;
          }
        }

        $output['staff_pick'] = $data->staff_pick;

        $output['facts']['problem'] = $data->facts['problem'] ? $data->facts['problem']['fact'] : NULL;
        $output['facts']['solution'] = $data->facts['solution'] ? $data->facts['solution']['fact'] : NULL;
        $output['facts']['sources'] = $data->facts['sources'] ? $data->facts['sources'] : NULL;

        $output['solutions'] = $data->solutions;

        $output['causes'] = $data->causes;

        $output['action_types'] = $data->action_types;

        $output['issue'] = $data->issue;

        $output['tags'] = $data->tags;

        $output['timing']['high_season'] = $data->timing['high_season'];
        $output['timing']['low_season'] = $data->timing['low_season'];

        $output['services'] = $data->services;

        $output['mobile_app']['dates'] = $data->mobile_app['dates'];
      }

      $output['reportback_info'] = $data->reportback_info;

      if ($data->uri) {
        $output['uri'] = $data->uri . '.json';
      }

    }

    return $output;
  }


  /**
   * Transform Kudos data and prepare for API response.
   *
   * @param object $data A Kudos object containing the following properties:
   *  - id: (string)
   *  - term: (array)
   *  - reportback_item: (array)
   *  - user: (array)
   *  - uri: (string)
   * @return array
   */
  protected function transformKudos($data) {
    $output = [
      'id' => $data->id,
      'term' => $data->term,
      'reportback_item' => $data->reportback_item,
      'user' => $data->user,
      'uri' => $data->uri . '.json',
    ];

    return $output;
  }


  /**
   * Transform Media data and prepare for API response.
   *
   * @param array $data
   * @param string|null $aspect_ratio
   * @return array|null
   */
  protected function transformMedia($data, $aspect_ratio = NULL) {
    if ($data['type'] === 'image') {
      $output = array();

      $output['uri'] = $data['sizes'][$aspect_ratio]['uri'];

      foreach ($data['sizes'] as $key => $size) {
        if (isset($size['uri'])) {
          $output['sizes'][$key]['uri'] = $size['uri'];
        }
      }

      $output['type'] = $data['type'];
      $output['dark_background'] = $data['dark_background'];

      return $output;
    }

    if ($data['type'] === 'video') {
      // @TODO: include video transformation code here!
    }

    return NULL;
  }


  /**
   * Transform Reportback data and prepare for API response.
   *
   * @param object $data Standard object or Reportback object.
   *   An object containing properties of Reportback data:
   *   - id: (string) The Reportback id.
   *   - created_at: (string) Date Reportback was created.
   *   - updated_at: (string) Date Reportback was updated.
   *   - quantity: (int) Quantity declared for Reportback.
   *   - why_participated: (string) Reason for participating.
   *   - flagged: (int) Status whether Reportback has been flagged.
   *   - reportback_items: (string) Comma separated list of Reportback Item ids for specified Reportback.
   * @return array
   */
  protected function transformReportback($data) {
    $output = [
      'id' => $data->id,
      'created_at' => $data->created_at,
      'updated_at' => $data->updated_at,
      'quantity' => $data->quantity,
    ];

    if (!isset($data->uri)) {
      $output['uri'] = services_resource_uri(['reportback', $data->id]) . '.json';
    }
    else {
      $output['uri'] = $data->uri . '.json';
    }

    if ($data instanceof Reportback) {
      $output['why_participated'] = $data->why_participated;
      $output['flagged'] = $data->flagged;

      try {
        $items = ReportbackItem::get($data->reportback_items);
        $items = services_resource_build_index_list($items, 'reportback-items', 'id');
      }
      catch (Exception $error) {
        $items = [];
      }

      $items = $this->transformCollection($items, 'transformReportbackItem');

      $output['reportback_items'] = [
        'total' => count($items),
        'data' => $items,
      ];
    }

    return $output;
  }


  /**
   * Transform Reportback Item data and prepare for API response.
   *
   * @param object $data
   *   A Reportback Item object containing following properties:
   *   - id: (string) Reportback Item id.
   *   - status: (string) Reportback Item status.
   *   - caption: (string) Reportback Item caption.
   *   - uri: (string) API URI for Reportback Item data.
   *   - media: (array) Reportback Item media.
   *   - created_at: (string) Date Reportback Item was created.
   *   - kudos: (array) Ids of kudos for Reportback Item.
   * @return array
   */
  protected function transformReportbackItem($data) {
    $output = [
      'id' => $data->id,
      'status' => $data->status,
      'caption' => $data->caption,
      'uri' => $data->uri . '.json',
      'media' => $data->media,
      'created_at' => $data->created_at,
    ];

    $kudos = $data->kudos ?: [];
    try {
      $kudos = Kudos::get($kudos);
      $kudos = dosomething_kudos_sort($kudos);
    }
    catch (Exception $error) {
      $kudos = [];
    }

    $output['kudos']['data'] = $kudos;

    return $output;
  }


  /**
   * Transform user data and prepare for API response.
   *
   * @param object $data
   *   An object containing properties of User data:
   *   - uid: (int) User id.
   * @return array
   */
  protected function transformUser($data) {
    return array(
      'id' => $data->id,
    );
  }

}
