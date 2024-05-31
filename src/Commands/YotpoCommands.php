<?php

namespace Drupal\yotpo\Commands;

use Drupal\yotpo\YotpoClient;
use Drush\Commands\DrushCommands;

/**
 * Yotpo commands.
 */
class YotpoCommands extends DrushCommands {

  /**
   * Yotpo client.
   *
   * @var \Drupal\yotpo\YotpoClient
   */
  protected YotpoClient $yotpoClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(YotpoClient $yotpoClient) {
    parent::__construct();
    $this->yotpoClient = $yotpoClient;
  }

  /**
   * Get products yotpo.
   *
   * @usage yotpo:get
   *   Get all products from yotpo.
   *
   * @command yotpo:get-products
   */
  public function getYotpoProducts() {
    $products = $this->yotpoClient->getYotpoProducts();
    $this->logger->notice('Products yotpo: {products}', ['products' => print_r($products, TRUE)]);
  }

  /**
   * Create/update product yotpo.
   *
   * @option bool update
   *   Update product if exists.
   * @usage yotpo:create --external_id=[SKU] --name=[NAME] --description=[DESC] --url=[URL] --price=[PRICE] --sku=[SKU] --update
   *   Get all products from yotpo.
   *
   * @command yotpo:create
   */
  public function createYotpoProduct(
    $options = [
      'update' => FALSE,
      'external_id' => NULL,
      'name' => NULL,
      'description' => NULL,
      'url' => NULL,
      'price' => NULL,
      'sku' => NULL,
    ],
  ) {
    if (empty($options['external_id'])) {
      $this->logger->notice('The external_id param is mandatory');
    }
    $product = [
      'external_id' => $options['external_id'],
      'sku' => $options['sku'],
      'name' => $options['name'],
      'description' => $options['description'],
      'url' => $options['url'],
      'price' => $options['price'],
    ];
    $product = array_filter($product);
    $created = $this->yotpoClient->createProduct($product, $options['update']);
    if (!$created) {
      $this->logger->notice('The product could not be created/updated');
    }
    else {
      $products_yotpo = $this->yotpoClient->getYotpoProducts(TRUE);
      $product_yotpo = $products_yotpo[$product['external_id']] ?? [];
      $this->logger->notice('Product updated: {product}', ['product' => print_r($product_yotpo, TRUE)]);
    }
  }

  /**
   * Get products yotpo reviews.
   *
   * @usage yotpo:get-reviews
   *   Get all products from yotpo.
   *
   * @command yotpo:get-reviews
   */
  public function getYotpoReviews() {
    $reviews = $this->yotpoClient->getProductReviews();
    $this->logger->notice('Reviews products yotpo: {reviews}', ['reviews' => print_r($reviews, TRUE)]);
  }

}
