<?php declare(strict_types=1);

namespace SwagPersonalProduct\Controller;

use GuzzleHttp\Client;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class PersonalProductController extends StorefrontController
{

    public const PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER = 'personal-product-image-url';

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepo;

    public function __construct(CartService $cartService, EntityRepositoryInterface $productRepo)
    {
        $this->cartService = $cartService;
        $this->productRepo = $productRepo;
    }

    /**
     * @Route("/checkout/personal-product/add", name="frontend.checkout.personal-product.add", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * @throws MissingRequestParameterException
     */
    public function addPersonalProduct(
        Cart $cart,
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response
    {
        /** @var string|null $unsplashUrl */
        $unsplashUrl = $requestDataBag->get(self::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER);

        if ($unsplashUrl === null) {
            throw new MissingRequestParameterException(self::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER);
        }

        $lineItems = $request->request->get('lineItems', []);
        /** @var array|false $product */
        $product = reset($lineItems);
        if ($product === false) {
            throw new MissingRequestParameterException('lineItems');
        }

        $productQuantity = (int)$product['quantity'];

        $personalProductLineItem = $this->createPersonalProductLineItem(
            $unsplashUrl,
            $product['id'],
            $productQuantity
        );

        $this->cartService->add($cart, $personalProductLineItem, $salesChannelContext);

        $this->addFlash('success', $this->trans('personalProduct.addToCart.success'));

        return $this->createActionResponse($request);

    }

    /**
     * @Route("/personal-product/getPersonalImage", name="frontend.personal-product.get-image", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     *
     */
    public function getPersonalImage(
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response
    {
        if(!$request->query->has('id'))
        {
            return new JsonApiResponse(['url' => 'https://picsum.photos/200/300']);
        }

        $id = (string) $request->query->get('id');

        $criteria = new Criteria([$id]);

        /** @var ProductEntity $product */
        $product = $this->productRepo->search($criteria, $salesChannelContext->getContext())->first();

        $customFields = $product->getCustomFields();

        $x0 = $customFields['personal_product_canvasX0'];
        $y0 = $customFields['personal_product_canvasY0'];
        $x1 = $customFields['personal_product_canvasX1'];
        $y1 = $customFields['personal_product_canvasY1'];

        $width = abs($x1-$x0);
        $height = abs($y1-$y0);
        $width = $this->roundToTens($width);
        $height = $this->roundToTens($height);

        $client = new Client(['allow_redirects' => false]);

        $location = $client->request('GET', 'https://picsum.photos/' . $width . '/' . $height)->getHeader('location')[0];

        return new JsonApiResponse(['url' => 'https://picsum.photos'.$location]);
    }

    private function roundToTens($n): int
    {
        $n = $n / 10;
        return (int) (round($n) * 10);
    }


    private function createPersonalProductLineItem(
        string $unsplashUrl,
        string $productId,
        int $productQuantity
    ): LineItem {
        $productLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            $productQuantity
        );

        $productLineItem->setPayloadValue('url', $unsplashUrl)
            ->setRemovable(true)
            ->setStackable(true);

        return $productLineItem;
    }
}
