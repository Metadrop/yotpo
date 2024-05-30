<?php

namespace Drupal\yotpo;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Yotpo client.
 */
class YotpoClient {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Access token.
   *
   * @var string
   */
  protected string $accessToken;

  /**
   * Base url.
   *
   * @var string
   */
  protected string $baseUrl = 'https://api.yotpo.com/core/v3/stores';

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Default options.
   *
   * @var array
   */
  protected array $defaultOptions = [
    'headers' => [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
      'X-Yotpo-Token' => '',
    ],
  ];

  /**
   * Yotpo Products.
   *
   * @var array
   */
  protected array $yotpoProducts;

  /**
   * Constructs a Client object.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerInterface $logger,
    ClientFactory $http_client_factory,
  ) {
    $this->config = $configFactory->get('yotpo.settings');
    $this->httpClient = $http_client_factory->fromOptions();
    $this->logger = $logger;
  }

  /**
   * Adds the base url and the credentials to the requests.
   */
  protected function setupCredentials() {
    $this->defaultOptions['headers']['X-Yotpo-Token'] = $this->getAccessToken();
  }

  /**
   * Add the default options to a set of options.
   *
   * If there is an option that is set in options and
   * default options, options has precedence.
   *
   * @param array $options
   *   Options.
   *
   * @return array
   *   Options mixed with the default options.
   */
  protected function addDefaultOptions(array $options) {
    foreach ($options as $key => $value) {
      if (isset($this->defaultOptions[$key]) && is_array($options[$key]) && is_array($this->defaultOptions[$key])) {
        $options[$key] = array_merge($this->defaultOptions[$key], $value);
      }
    }
    $options += $this->defaultOptions;
    $additional_headers = $this->getAdditionalHeaders();
    $headers = isset($options['headers']) ? array_merge($options['headers'], $additional_headers) : $additional_headers;
    $options['headers'] = $headers;
    return $options;
  }

  /**
   * Get the additional headers from the settings.
   *
   * @return array
   *   Keys are the header names, values are the header values.
   */
  protected function getAdditionalHeaders() {
    $additional_headers = $this->config->get('additional_headers') ?? [];
    $additional_headers_map = [];
    if (!empty($additional_headers)) {
      foreach ($additional_headers as $additional_header) {
        [$name, $value] = explode('|', $additional_header);
        $additional_headers_map[$name] = $value;
      }
    }
    return $additional_headers_map;
  }

  /**
   * Request Yotpo API.
   *
   * @return array
   *   Response array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function callYotpoApi(
    string $endpoint,
    string $method = 'GET',
    array $additional_options = [],
  ): array {
    if (!str_contains($endpoint, 'access')) {
      $this->setupCredentials();
    }

    $options = $this->addDefaultOptions($additional_options);

    try {

      $response = $this->httpClient->request($method, $this->baseUrl . '/' . $this->config->get('api_key') . '/' . $endpoint, $options);
      $response_body = (string) $response->getBody();
    }
    catch (\Exception $e) {
      // 7. Handle exceptions.
      if ($e instanceof BadResponseException) {
        $error_response = json_decode((string) $e->getResponse()->getBody());
        $exception = $this->getExceptionForError($e, $error_response);
      }
      else {
        $exception = $e;
      }
      $this->logger->log(LogLevel::ERROR, sprintf('Failed %s. Exception: %s', $endpoint, $exception->getMessage()));

      throw $exception;
    }

    $response_array = json_decode($response_body, TRUE) ?? [];
    return $response_array;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExceptionForError(\Exception $exception, object $error_response): \Exception {
    return $exception;
  }

  /**
   * Access token.
   */
  protected function getAccessToken() {
    if (empty($this->accessToken)) {
      $secret = [
        'secret' => $this->config->get('api_secret'),
      ];
      $options = [
        'body' => json_encode($secret),
      ];
      $access_token_json = $this->callYotpoApi('access_tokens', 'POST', $options);
      $this->accessToken = $access_token_json['access_token'] ?? '';
    }

    return $this->accessToken;
  }

  /**
   * Create product.
   */
  public function createProduct(array $product, $update = FALSE) {
    $yotpoProducts = $this->getYotpoProducts();
    $product_attributes = [
      'name' => $product['name'] ?? NULL,
      'description' => $product['description'] ?? NULL,
      'url' => $product['url'] ?? NULL,
      'price' => $product['price'] ?? NULL,
    ];

    $product_yotpo_array = [
      'product' => array_filter($product_attributes),
    ];

    if (!in_array($product['external_id'], array_keys($yotpoProducts))) {
      $product_yotpo_array['product']['external_id'] = $product['external_id'];
      $product_yotpo_array['product']['sku'] = $product['sku'] ?? NULL;
      $product_json = json_encode($product_yotpo_array);
      $options = [
        'body' => $product_json,
      ];
      $this->callYotpoApi('products', 'POST', $options);
      return TRUE;
    }
    elseif ($update) {
      $product_json = json_encode($product_yotpo_array);
      $options = [
        'body' => $product_json,
      ];
      $this->callYotpoApi('products/' . $yotpoProducts[$product['external_id']]['yotpo_id'], 'PATCH', $options);
      return TRUE;
    }
    return FALSE;

  }

  /**
   * Yotpo products.
   */
  public function getYotpoProducts($update_list = FALSE) {
    if (empty($this->yotpoProducts) || $update_list) {

      $products = $this->callYotpoApi('products');

      $products_yotpo = $products['products'] ?? [];
      $list = [];
      foreach ($products_yotpo as $product) {
        if ($product['external_id']) {
          $list[$product['external_id']] = $product;
        }
      }
      $this->yotpoProducts = $list;
    }
    return $this->yotpoProducts;
  }

}
