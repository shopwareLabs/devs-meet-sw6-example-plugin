# Personal Product Plugin
## Setup - getting base plugin
- download plugin zip from link
- unpack plugin to custom/plugins folder
- show plugin skeleton
    - show composer json
    - possibility to add dependencies
- go through plugin files and explain folders/files

## Adding custom fields
- open `src/SwagPersonalProduct.php` and add explain custom fields
- use `bin/console plugin:install --activate SwagPersonalProduct` to install and activate plugin

## Example 01 - Product detail page extension
- ask about Vue.js 
- how can we find components
- ask about developer console
- search the component in the administration with Vue console `sw-product-detail-base`
- show global shopware object
- create extension
`src/Resources/app/administration/src/extension/sw-product-detail-base/sw-product-detail-base.html.twig`:
```html
{% block sw_product_detail_base_basic_info_card %}
    <sw-card title="Product customizer attributes" :isLoading="isLoading">
        {#<personal-product-canvas></personal-product-canvas>#}
        Test Output
    </sw-card>
    {% parent() %}
{% endblock %}
```
- Add component to
`src/Resources/app/administration/src/main.js`
```javascript
import './extension/sw-product-detail-base';
```

### New admin component for canvas rendering
- open and explain product canvas component
`src/Resources/app/administration/src/component/personal-product-canvas/index.js`
- explain vuex store and add computed state and getter (explain and show shopware object)
- take a look in the vue developer console to show product entity
- explain und build computed setter/getter

## Example 02 - Storefront product viewer
- Show JS base plugin and the corresponding template
- dump `page.product`
- use the template to initialize plugin and handle over the coordinates
`src/Resources/views/storefront/page/product-detail/index.html.twig`
```html
    {% set media = page.product.media.first().media %}

    <div class="personal-product"
         data-personal-product-viewer="true"
         data-personal-product-viewer-options='
     {
        "x0":{{ customFields.personal_product_canvasX0 }},
        "y0":{{ customFields.personal_product_canvasY0 }},
        "x1":{{ customFields.personal_product_canvasX1 }},
        "y1":{{ customFields.personal_product_canvasY1 }},
        "baseImage":"{{ media.url }}"
     }'>
        <canvas
            class="personal-product-canvas"
            width="{{ media.metaData.width }}"
            height="{{ media.metaData.height }}">
        </canvas>
    </div>
```
- Register plugin in the plugin manager
`src/Resources/app/storefront/src/main.js`
```javascript
    PluginManager.register('PersonalProductViewer', PersonalProductViewer, '[data-personal-product-viewer]');
```
- test plugin with a image url from google

# Rades part

## Image change component, enabling Fetch button 
`src/Resources/app/storefront/src/js/image-changer.plugin.js`  
```javascript
onFetchedImage(response) {
    console.log(response)
}
```
      
## Reading custom fields
`src/Service/ImageService.php`
```php
   public function getRandomUrlByProductId(string $productId, SalesChannelContext $salesChannelContext): string
   {
       $criteria = new Criteria([$productId]);

       /** @var ProductEntity $product */
       $product = $this->productRepository->search($criteria, $salesChannelContext->getContext())->first();

       $width = $this->getPersonalImageWidth($product);

       $height = $this->getPersonalImageHeight($product);

       return $this->imageGuesser->fetchRandomImageUrl($width, $height);
   }

   private function getPersonalImageWidth(ProductEntity $product): int
   {
       $customFields = $product->getCustomFields();

       $customizable = $customFields[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE] ?? null;
       $x0 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_X0] ?? null;
       $x1 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_X1] ?? null;

       if ($customizable !== true || $x0 === null || $x1 === null) {
           throw new ProductNotCustomizable($product->getId());
       }

       $width = abs($x1 - $x0);

       return $this->roundToTens($width);
   }

   private function getPersonalImageHeight(ProductEntity $product): int
   {
       $customFields = $product->getCustomFields();

       $customizable = $customFields[SwagPersonalProduct::PRODUCT_CUSTOMIZABLE] ?? null;
       $y0 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_Y0] ?? null;
       $y1 = $customFields[SwagPersonalProduct::PRODUCT_CANVAS_Y1] ?? null;

       if ($customizable !== true || $y0 === null || $y1 === null) {
           throw new ProductNotCustomizable($product->getId());
       }

      $height = abs($y1 - $y0);

      return $this->roundToTens($height);
   }
```
## Image changer communication with image viewer
`src/Resources/app/storefront/src/js/image-changer.plugin.js`  
```javascript
    onFetchedImage(response) {
        const url = JSON.parse(response).url;
        this._input.value = url;
        this.publishChangedEvent(url);
    }
    
    publishChangedEvent(newImageUrl) {
        this.$emitter.publish('imageChanged', newImageUrl);
    }
```
`src/Resources/app/storefront/src/js/personal-product-viewer.plugin.js`
```javascript
    init() {
        ...
    
        this.subscribeImageChangedEvent();
    } 
    subscribeImageChangedEvent() {
        // Subscribe to image changer plugin event
        const imageChangerEl = DomAccess.querySelector(document, '[data-image-changer]');
        const imageChangerInstance = this.PluginManager.getPluginInstanceFromElement(imageChangerEl, 'ImageChanger');
        imageChangerInstance.$emitter.subscribe('imageChanged', this.onChangeImage.bind(this));
      }
```
      
## Adding the PersonalImage
`src/PersonalImage/PersonalImageDefinition.php`
```php
    <?php declare(strict_types=1);
    
    namespace SwagPersonalProduct\PersonalImage;
    
    use Shopware\Core\Content\Product\ProductDefinition;
    use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
    use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
    use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
    
    class PersonalImageDefinition extends EntityDefinition
    {
        public const ENTITY_NAME = 'swag_personal_product_image';
    
        public function getEntityName(): string
        {
            return self::ENTITY_NAME;
        }
    
        public function getEntityClass(): string
        {
            return PersonalImageEntity::class;
        }
    
        public function getCollectionClass(): string
        {
            return PersonalImageCollection::class;
        }
    
        protected function defineFields(): FieldCollection
        {
            return new FieldCollection([
                (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
                (new StringField('url', 'url'))->addFlags(new Required()),
                (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
    
                (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class)),
            ]);
        }
    }
```
  
`src/Extension/ProductExtension.php`
```php
  <?php declare(strict_types=1);
  
  namespace SwagPersonalProduct\Extension;
  
  use Shopware\Core\Content\Product\ProductDefinition;
  use Shopware\Core\Framework\DataAbstractionLayer\EntityExtensionInterface;
  use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
  use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
  use SwagPersonalProduct\PersonalImage\PersonalImageDefinition;
  
  class ProductExtension implements EntityExtensionInterface
  {
      public function extendFields(FieldCollection $collection): void
      {
          $collection->add(
              new OneToManyAssociationField(
                  'swagPersonalProductImage',
                  PersonalImageDefinition::class,
                  'id'
              )
          );
      }
  
      public function getDefinitionClass(): string
      {
          return ProductDefinition::class;
      }
  }
```

`src/Resources/config/services.xml`
```xml
    ...
    <service id="SwagPersonalProduct\Service\PersonalProductLineItemService">
       <argument id="Shopware\Core\Checkout\Cart\SalesChannel\CartService" type="service"/>
       <argument id="swag_personal_product_image.repository" type="service"/>
    </service>
    
    <service id="SwagPersonalProduct\PersonalImage\PersonalImageDefinition">
       <tag name="shopware.entity.definition" entity="swag_personal_product_image"/>
    </service>
    
    <service id="SwagPersonalProduct\Extension\ProductExtension">
       <tag name="shopware.entity.extension"/>
    </service>
``` 
   
## Adding the OrderLineItem
`src/Service/PersonalProductLineItemService.php`
```php
   public function __construct(
       CartService $cartService,
       EntityRepositoryInterface $imageRepository
   ) {
       $this->cartService = $cartService;
       $this->imageRepository = $imageRepository;
   }
   public function add(Cart $cart, string $imageUrl, string $productId, int $productQuantity, SalesChannelContext $salesChannelContext): void
   {
       $lineItem = $this->createPersonalProductLineItem($imageUrl, $productId, $productQuantity, $salesChannelContext);

       $this->cartService->add($cart, $lineItem, $salesChannelContext);
   }

   private function createPersonalProductLineItem(
       string $imageUrl,
       string $productId,
       int $productQuantity,
       SalesChannelContext $context
   ): LineItem {
       $lineItemId = $this->getLineItemId($productId, $imageUrl, $context);

       $productLineItem = new LineItem(
           $lineItemId,
           LineItem::PRODUCT_LINE_ITEM_TYPE,
           $productId,
           $productQuantity
       );

       $productLineItem->setPayloadValue('url', $imageUrl)
           ->setRemovable(true)
           ->setStackable(true);

       return $productLineItem;
   }

   private function getLineItemId(string $productId, string $imageUrl, SalesChannelContext $context): string
   {
       $criteria = new Criteria();
       $criteria->setLimit(1)->addFilter(new EqualsFilter('productId', $productId), new EqualsFilter('url', $imageUrl));
       $id = $this->imageRepository->searchIds($criteria, $context->getContext())->firstId();

       if ($id !== null) {
           return $id;
       }

       $id = Uuid::randomHex();

       $this->imageRepository->create([
           [
               'id' => $id,
               'productId' => $productId,
               'url' => $imageUrl,
           ],
       ], $context->getContext());

       return $id;
   }
```

## Adding a filter
`src/Core/ProductFilterSubscriber.php`
```php
    public function handleFilter(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();

        $criteria = $event->getCriteria();
        $criteria->addAggregation(
            new FilterAggregation(
                self::FILTER_NAME,
                new MaxAggregation(self::REQUEST_PARAMETER, self::PRODUCT_FILTER_FIELD),
                [new EqualsFilter(self::PRODUCT_FILTER_FIELD, true)]
            )
        );

        $filtered = $request->get(self::REQUEST_PARAMETER);

        if (!$filtered) {
            return;
        }

        $criteria->addPostFilter(new EqualsFilter(self::PRODUCT_FILTER_FIELD, true));
    }
```
