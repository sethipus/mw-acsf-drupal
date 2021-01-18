<?php

namespace Drupal\Tests\salsify_integration\Unit;

use Drupal\Component\Serialization\Json;
use Drupal\salsify_integration\ProductHelper;
use Drupal\salsify_integration\SalsifyFieldsMap;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\salsify_integration\ProductHelper
 * @group mars
 * @group salsify_integration
 */
class ProductHelperTest extends UnitTestCase {

  /**
   * System under test.
   *
   * @var \Drupal\salsify_integration\ProductHelper
   */
  private $productHelper;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->productHelper = new ProductHelper();
  }

  /**
   * Test.
   */
  public function testShouldIsProductVariant() {
    $product = [
      'CMS: content type' => 'product_variant',
    ];

    $result = $this->productHelper::isProductVariant($product);
    $this->assertTrue($result);
  }

  /**
   * Test.
   */
  public function testShouldIsProductMultipack() {
    $product = [
      'CMS: content type' => 'product_multipack',
    ];

    $result = $this->productHelper::isProductMultipack($product);
    $this->assertTrue($result);
  }

  /**
   * Test.
   */
  public function testShouldIsProduct() {
    $product = [
      'CMS: content type' => 'product',
    ];

    $result = $this->productHelper::isProduct($product);
    $this->assertTrue($result);
  }

  /**
   * Test.
   */
  public function testShouldGetProductType() {
    $product = [
      'CMS: content type' => 'product',
    ];

    $result = $this->productHelper::getProductType($product);
    $this->assertSame(ProductHelper::PRODUCT_CONTENT_TYPE, $result);

    $product_multipack = [
      'CMS: content type' => 'product_multipack',
    ];

    $result = $this->productHelper::getProductType($product_multipack);
    $this->assertSame(ProductHelper::PRODUCT_MULTIPACK_CONTENT_TYPE, $result);

    $product_variant = [
      'CMS: content type' => 'product_variant',
    ];

    $result = $this->productHelper::getProductType($product_variant);
    $this->assertSame(ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE, $result);
  }

  /**
   * Test.
   */
  public function testShouldValidateDataRecord() {
    $product = [
      'GTIN' => 'value',
    ];
    $field_mapping = [
      'salsify_data_type' => 'string',
      'salsify_id' => 'GTIN',
    ];

    $result = $this->productHelper->validateDataRecord($product, $field_mapping);
    $this->assertTrue($result);
  }

  /**
   * Test.
   */
  public function testShouldGetProductsData() {
    $products = Json::encode([
      'data' => [
        'product 1',
        'product 2',
      ],
    ]);

    foreach ($this->productHelper->getProductsData($products) as $product) {
      $this->assertNotEmpty($product);
    }
  }

  /**
   * Test.
   */
  public function testShouldFilterProductsInResponse() {
    $products = Json::encode([
      'data' => [
        [
          'mapping' => [
            'Bazaarvoice Family ID' => 'Family ID',
            'GTIN' => 'value_1',
            'Send to Brand Site?' => TRUE,
          ],
          'salsify:id' => '123',
        ],
        [
          'mapping' => [
            'Bazaarvoice Family ID' => 'Family ID',
            'GTIN' => 'value_2',
            'Case Net Weight' => 'value',
            'CMS: Variety' => 'no',
            'Send to Brand Site?' => FALSE,
          ],
          'salsify:id' => '123',
        ],
        [
          'mapping' => [
            'Bazaarvoice Family ID' => 'Family ID',
            'GTIN' => 'value_3',
            'CMS: Variety' => 'yes',
            'Send to Brand Site?' => TRUE,
          ],
          'salsify:id' => '123',
        ],
      ],
    ]);

    $response = $this->productHelper->filterProductsInResponse($products);
    $this->assertNotEmpty($response);
    $this->assertIsString($response);
    $this->assertSame(
      2,
      count(Json::decode($response)['data'])
    );
  }

  /**
   * Test.
   */
  public function testShouldFilterProductFields() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'no',
          'Send to Brand Site?' => FALSE,
          'salsify:id' => '123',
          'salsify:version' => 'version',
          'salsify:system_id' => 'system_id',
          'salsify:created_at' => time(),
          'salsify:updated_at' => time(),
          'salsify:digital_assets' => [],
        ],
      ],
    ]);

    $response = $this->productHelper->filterProductFields($products);
    $this->assertNotEmpty($response);
    $this->assertIsString($response);
  }

  /**
   * Test.
   */
  public function testShouldAddProducts() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'no',
          'Send to Brand Site?' => FALSE,
          'salsify:id' => '123',
          'salsify:version' => 'version',
          'salsify:system_id' => 'system_id',
          'salsify:created_at' => time(),
          'salsify:updated_at' => time(),
          'salsify:digital_assets' => [],
          'CMS: Product Variant Family ID' => 'family id',
        ],
      ],
    ]);

    $response = $this->productHelper->addProducts($products);
    $this->assertNotEmpty($response);
    $this->assertIsString($response);
  }

  /**
   * Test.
   */
  public function testShouldAddProductMultipack() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'Trade Item Description' => 'title',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'yes',
          'CMS: content type' => ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE,
          'Send to Brand Site?' => FALSE,
          'salsify:id' => '123',
          'salsify:version' => 'version',
          'salsify:system_id' => 'system_id',
          'salsify:created_at' => time(),
          'salsify:updated_at' => time(),
          'salsify:digital_assets' => [],
          'CMS: Product Variant Family ID' => 'family id',
          'Calcium 2' => 2,
          'Calcium 3' => 4,
          'Calcium 4' => 5,
          'Iron 2' => 2,
          'Ironv 3' => 4,
          'Iron 4' => 5,
          'CMS: Product Name' => 'product_name',
        ],
      ],
    ]);

    $response = $this->productHelper->addProductMultipacks($products);
    $this->assertNotEmpty($response);
    $this->assertIsString($response);
  }

  /**
   * Test.
   */
  public function testShouldGetPrimaryMapping() {

    $response = $this->productHelper->getPrimaryMapping();
    $this->assertEmpty($response);
    $this->assertIsArray($response);
  }

  /**
   * Test.
   */
  public function testShouldCreateProductFromProductVariant() {
    $product = [
      'Bazaarvoice Family ID' => 'Family ID',
      'GTIN' => 'value_2',
      'Case Net Weight' => 'value',
      'CMS: Variety' => 'no',
      'Send to Brand Site?' => FALSE,
      'salsify:id' => '123',
      'salsify:version' => 'version',
      'salsify:system_id' => 'system_id',
      'salsify:created_at' => time(),
      'salsify:updated_at' => time(),
      'salsify:digital_assets' => [],
      'CMS: Product Variant Family ID' => 'family id',
    ];

    $product = $this->productHelper->createProductFromProductVariant(
      ProductHelper::PRODUCT_CONTENT_TYPE,
      SalsifyFieldsMap::SALSIFY_FIELD_MAPPING_PRODUCT,
      $product
    );
    $this->assertNotEmpty($product);
    $this->assertIsArray($product);
  }

  /**
   * Test.
   */
  public function testShouldGetNuntritionFiledsByName() {
    $product = [
      'Bazaarvoice Family ID' => 'Family ID',
      'GTIN' => 'value_2',
      'Case Net Weight' => 'value',
      'CMS: Variety' => 'no',
      'Send to Brand Site?' => FALSE,
      'salsify:id' => '123',
      'salsify:version' => 'version',
      'salsify:system_id' => 'system_id',
      'salsify:created_at' => time(),
      'salsify:updated_at' => time(),
      'salsify:digital_assets' => [],
      'CMS: Product Variant Family ID' => 'family id',
      'Nutrition field 2' => 2,
      'Nutrition field 3' => 4,
      'Nutrition field 4' => 5,
    ];

    $product = $this->productHelper->getNuntritionFiledsByName(
      'Nutrition field',
      $product
    );
    $this->assertNotEmpty($product);
    $this->assertIsArray($product);
  }

  /**
   * Test.
   */
  public function testShouldAddNutritionFieldsData() {
    $product_variant = [
      'Bazaarvoice Family ID' => 'Family ID',
      'GTIN' => 'value_2',
      'Case Net Weight' => 'value',
      'CMS: Variety' => 'no',
      'Send to Brand Site?' => FALSE,
      'salsify:id' => '123',
      'salsify:version' => 'version',
      'salsify:system_id' => 'system_id',
      'salsify:created_at' => time(),
      'salsify:updated_at' => time(),
      'salsify:digital_assets' => [],
      'CMS: Product Variant Family ID' => 'family id',
      'Nutrition field 2' => 2,
      'Nutrition field 3' => 4,
      'Nutrition field 4' => 5,
    ];
    $product = [];

    $this->productHelper->addNutritionFieldsData(
      [
        'Nutrition field 2',
        'Nutrition field 3',
        'Nutrition field 4',
      ],
      $product,
      $product_variant
    );
    $this->assertNotEmpty($product);
    $this->assertIsArray($product);
  }

  /**
   * Test.
   */
  public function testShouldCreateNutritionProductsFromProductVariant() {
    $product_variant = [
      'Bazaarvoice Family ID' => 'Family ID',
      'Trade Item Description' => 'title',
      'GTIN' => 'value_2',
      'Case Net Weight' => 'value',
      'CMS: Variety' => 'no',
      'Send to Brand Site?' => FALSE,
      'salsify:id' => '123',
      'salsify:version' => 'version',
      'salsify:system_id' => 'system_id',
      'salsify:created_at' => time(),
      'salsify:updated_at' => time(),
      'salsify:digital_assets' => [],
      'CMS: Product Variant Family ID' => 'family id',
      'Calcium 2' => 2,
      'Calcium 3' => 4,
      'Calcium 4' => 5,
      'Iron 2' => 2,
      'Ironv 3' => 4,
      'Iron 4' => 5,
      'CMS: Product Name' => 'product_name',
    ];

    $result = $this->productHelper->createNutritionProductsFromProductVariant(
      $product_variant
    );
    $this->assertNotEmpty($result);
    $this->assertIsArray($result);
  }

  /**
   * Test.
   */
  public function testShouldFillMappingByGeneratedProducts() {
    $generated_products = [
      [
        'salsify:id' => '123',
        'CMS: content type' => ProductHelper::PRODUCT_CONTENT_TYPE,
      ],
      [
        'salsify:id' => '1234',
        'CMS: content type' => ProductHelper::PRODUCT_VARIANT_CONTENT_TYPE,
      ],
    ];

    $this->productHelper->fillMappingByGeneratedProducts(
      $generated_products,
      'multipack_id'
    );
    $mapping = $this->productHelper->getPrimaryMapping();
    $this->assertNotEmpty($mapping);
    $this->assertIsArray($mapping);
  }

  /**
   * Test.
   */
  public function testShouldGetAttributesByProducts() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_1',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'no',
          'Send to Brand Site?' => FALSE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_3',
          'CMS: Variety' => 'yes',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
        ],
      ],
    ]);

    $attributes = $this->productHelper->getAttributesByProducts($products);
    $this->assertIsArray($attributes);
    $this->assertNotEmpty($attributes);
  }

  /**
   * Test.
   */
  public function testShouldGetAttributeValuesByProducts() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_1',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'CMS: Category Computed' => 'category1',
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'no',
          'Send to Brand Site?' => FALSE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'CMS: Category Computed' => 'category1',
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_3',
          'CMS: Variety' => 'yes',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'CMS: Category Computed' => 'category1',
        ],
      ],
    ]);

    $attributes = $this->productHelper->getAttributeValuesByProducts($products);
    $this->assertIsArray($attributes);
    $this->assertNotEmpty($attributes);
  }

  /**
   * Test.
   */
  public function testShouldGetDigitalAssetsByProducts() {
    $products = Json::encode([
      'data' => [
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_1',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'Brand Name' => 'name1',
          'salsify:digital_assets' => [
            ['salsify:id' => '123'],
          ],
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_2',
          'Case Net Weight' => 'value',
          'CMS: Variety' => 'no',
          'Send to Brand Site?' => FALSE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'Brand Name' => 'name2',
          'salsify:digital_assets' => [
            ['salsify:id' => '345'],
          ],
        ],
        [
          'Bazaarvoice Family ID' => 'Family ID',
          'GTIN' => 'value_3',
          'CMS: Variety' => 'yes',
          'Send to Brand Site?' => TRUE,
          'Dietary Fiber' => 'value',
          'Generic Product Description' => 'description',
          'Brand Name' => 'name3',
          'salsify:digital_assets' => [
            ['salsify:id' => '456'],
          ],
        ],
      ],
    ]);

    $assets = $this->productHelper->getDigitalAssetsByProducts($products);
    $this->assertIsArray($assets);
    $this->assertNotEmpty($assets);
  }

  /**
   * Test.
   */
  public function testShouldSortProducts() {
    $products = [
      [
        'Bazaarvoice Family ID' => 'Family ID',
        'GTIN' => 'value_1',
        'Send to Brand Site?' => TRUE,
        'Dietary Fiber' => 'value',
        'Generic Product Description' => 'description',
        'Brand Name' => 'name1',
        'CMS: content type' => 'product',
        'salsify:digital_assets' => [
          ['salsify:id' => '123'],
        ],
      ],
      [
        'Bazaarvoice Family ID' => 'Family ID',
        'GTIN' => 'value_2',
        'Case Net Weight' => 'value',
        'CMS: Variety' => 'no',
        'Send to Brand Site?' => FALSE,
        'Dietary Fiber' => 'value',
        'Generic Product Description' => 'description',
        'Brand Name' => 'name2',
        'CMS: content type' => 'product_variant',
        'salsify:digital_assets' => [
          ['salsify:id' => '345'],
        ],
      ],
      [
        'Bazaarvoice Family ID' => 'Family ID',
        'GTIN' => 'value_3',
        'CMS: Variety' => 'yes',
        'Send to Brand Site?' => TRUE,
        'Dietary Fiber' => 'value',
        'Generic Product Description' => 'description',
        'Brand Name' => 'name3',
        'CMS: content type' => 'product_multipack',
        'salsify:digital_assets' => [
          ['salsify:id' => '456'],
        ],
      ],
    ];

    $this->productHelper->sortProducts($products);
    $this->assertSame(
      'value_2',
      reset($products)['GTIN']
    );
  }

}
