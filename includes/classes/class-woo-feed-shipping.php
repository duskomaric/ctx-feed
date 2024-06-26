<?php
class Woo_Feed_Shipping_Pro extends Woo_Feed_Shipping {
    /**
     * @var WC_Product $product Contain product object.
     */
    private $product;

    /**
     * @var array $settings Contain plugin setting.
     */
    private $settings;

    /**
     * @var string $currency Currency sign like `USD`, `EUR`.
     */
    private $currency;

    /**
     * @var string $class_cost_id Shipping class cost id.
     */
    private $class_cost_id;
    /**
     * @var array $shipping_zones Contain Shipping Zone info.
     */
    private $shipping_zones;
    /**
     * @var string $country
     */
    private $feed_country;
    /**
     * @var array $config Contain feed configuration.
     */
    private $config;

    /**
     * @var mixed $obj_v3_pro Contain pro version v3 object.
     */
    private $obj_v3_pro;

    /**
     * @var mixed $obj_v3 Contain free version v3 object.
     */
    public $obj_v3;
    function __construct($feed_config) {
        parent::__construct($feed_config);
        $this->config = $feed_config;
        $this->obj_v3_pro = new Woo_Feed_Products_v3_Pro($this->config);
        add_filter("woo_feed_filter_object_v3", array($this, "woo_feed_filter_object_v3"));
        add_filter("woo_feed_get_attribute_value_for_shipping", array($this, "woo_feed_get_attribute_value_for_shipping"), 10, 4);
    }

    /**
     * @param $obj_v3
     * @return Woo_Feed_Products_v3_Pro return pro v3 object
     */
    function woo_feed_filter_object_v3($obj_v3) {
        return $this->obj_v3_pro;
    }

    /**
     * @param mixed $attributeValue attribute value.
     * @param WC_Product $product Contain product object.
     * @param string $attribute product attribute.
     * @param string $mattributes merchant attribute.
     *
     * @since 5.3.7
     * @return string $attributeValue attribute value.
     */
    function woo_feed_get_attribute_value_for_shipping($attributeValue, $product, $attribute, $mattributes) {
        return $this->obj_v3_pro->getAttributeValueByType($product, $attribute, $mattributes);
    }
}