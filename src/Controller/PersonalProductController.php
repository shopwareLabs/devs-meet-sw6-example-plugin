<?php declare(strict_types=1);

namespace SwagPersonalProduct\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
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

    public const PERSONAL_PRODUCT_REQUEST_UNSPLASH_URL_PARAMETER = 'personal-product-unsplash-url';

    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
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
        $unsplashUrl = $requestDataBag->get(self::PERSONAL_PRODUCT_REQUEST_UNSPLASH_URL_PARAMETER);

        if ($unsplashUrl === null) {
            throw new MissingRequestParameterException(self::PERSONAL_PRODUCT_REQUEST_UNSPLASH_URL_PARAMETER);
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
