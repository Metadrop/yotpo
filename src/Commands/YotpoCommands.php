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
   * @command yotpo:get
   */
  public function getYotpoProducts() {
    $products = $this->yotpoClient->getYotpoProducts();
    $this->logger->notice('Products yotpo: {products}', ['products' => print_r($products, TRUE)]);
  }

}
