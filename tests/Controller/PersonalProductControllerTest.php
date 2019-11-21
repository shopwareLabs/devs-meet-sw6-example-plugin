<?php declare(strict_types=1);

namespace SwagPersonalProduct\Test\Controller;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagPersonalProduct\Controller\PersonalProductController;
use SwagPersonalProduct\Service\ImageGuesser;
use SwagPersonalProduct\Service\ImageService;
use SwagPersonalProduct\Service\PersonalProductLineItemService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PersonalProductControllerTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ImageGuesser
     */
    private $imageGuesser;

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * @var PersonalProductLineItemService
     */
    private $personalProductLineItemService;

    protected function setUp(): void
    {
        $this->salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
        $this->cartService = $this->getContainer()->get(CartService::class);
        $this->productRepository = $this->getContainer()->get('product.repository');
        $this->imageGuesser = $this->getContainer()->get(ImageGuesser::class);
        $this->imageService = $this->getContainer()->get(ImageService::class);
        $this->personalProductLineItemService = $this->getContainer()->get(PersonalProductLineItemService::class);
    }

    public function testAddPersonalProductMissingParameter(): void
    {
        $controller = $this->createController();

        $cart = new Cart('testCart', 'token');
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->expectException(MissingRequestParameterException::class);
        $this->expectExceptionMessage(sprintf(
            'Parameter "%s" is missing.',
            PersonalProductController::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER
        ));
        $request = $this->createRequest(Uuid::randomHex(), Uuid::randomHex());
        $request->request->remove(PersonalProductController::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER);
        $controller->addPersonalProduct(
            $cart,
            $request,
            $salesChannelContext
        );
    }

    public function testAddPersonalProductMissingProduct(): void
    {
        $controller = $this->createController();

        $cart = new Cart('testCart', 'token');
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $request = $this->createRequest(Uuid::randomHex(), Uuid::randomHex());
        $request->request->remove('lineItems');

        $this->expectException(MissingRequestParameterException::class);
        $this->expectExceptionMessage(sprintf('Parameter "%s" is missing.', 'lineItems'));
        $controller->addPersonalProduct(
            $cart,
            $request,
            $salesChannelContext
        );
    }

    public function testAddPersonalProduct(): void
    {
        $controller = $this->createController();

        $productId = $this->createProduct();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $cart = $this->cartService->createNew(Uuid::randomHex());
        $request = $this->createRequest($productId, 'https://picsum.photos/id/200/300');

        $controller->addPersonalProduct(
            $cart,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart($cart->getToken(), $salesChannelContext);

        static::assertCount(0, $filledCart->getErrors());
        static::assertCount(1, $filledCart->getLineItems());
        $personalLineItem = $filledCart->getLineItems()->first();
        static::assertNotNull($personalLineItem);
        static::assertSame($productId, $personalLineItem->getReferencedId());
        static::assertTrue($personalLineItem->isRemovable());
        static::assertTrue($personalLineItem->isStackable());
    }

    public function testGetRandomUrl(): void
    {
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        $id = Uuid::randomHex();
        $imageService = $this->createMock(ImageService::class);
        $imageService->expects(static::once())->method('getRandomUrlByProductId')->with($id, $salesChannelContext)->willReturn('http://image.url');

        $controller = new PersonalProductController($this->createMock(PersonalProductLineItemService::class), $imageService);

        $urlResponse = $controller->getPersonalImage($id, $salesChannelContext);

        static::assertInstanceOf(JsonResponse::class, $urlResponse);
        $json = json_decode($urlResponse->getContent(), true);

        static::assertCount(1, $json);
        static::assertArrayHasKey('url', $json);
        static::assertSame('http://image.url', $json['url']);
    }

    private function createProduct(): string
    {
        $productId = Uuid::randomHex();

        $this->productRepository->create([
            [
                'id' => $productId,
                'stock' => 10,
                'name' => Uuid::randomHex(),
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 10,
                        'net' => 8,
                        'linked' => false,
                    ],
                ],
                'visibilities' => [
                    [
                        'salesChannelId' => Defaults::SALES_CHANNEL,
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
                'productNumber' => Uuid::randomHex(),
                'taxId' => $this->getValidTaxId(),
            ],
        ], Context::createDefaultContext());

        return $productId;
    }

    private function getValidTaxId(): string
    {
        $taxRepository = $this->getContainer()->get('tax.repository');
        $criteria = new Criteria();
        $criteria->setLimit(1);

        return $taxRepository->search($criteria, Context::createDefaultContext())
            ->first()
            ->getId();
    }

    private function createController(): PersonalProductController
    {
        $controller = new PersonalProductController($this->personalProductLineItemService, $this->imageService);
        $controller->setContainer($this->getContainer());

        return $controller;
    }

    private function createRequest(string $productId, string $url): Request
    {
        return new Request([], [
            'lineItems' => [
                $productId => [
                    'quantity' => 5,
                    'id' => $productId,
                    'referencedId' => $productId,
                ],
            ],
            PersonalProductController::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER => $url,
        ]);
    }
}
