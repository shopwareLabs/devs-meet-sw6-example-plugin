<?php declare(strict_types=1);

namespace SwagPersonalProduct\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use SwagPersonalProduct\Service\ImageService;
use SwagPersonalProduct\Service\PersonalProductLineItemService;
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
     * @var PersonalProductLineItemService
     */
    private $personalProductLineItemService;

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(
        PersonalProductLineItemService $personalProductLineItemService,
        ImageService $imageService
    ) {
        $this->personalProductLineItemService = $personalProductLineItemService;
        $this->imageService = $imageService;
    }

    /**
     * @Route("/checkout/personal-product/add", name="frontend.checkout.personal-product.add", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * @throws MissingRequestParameterException
     */
    public function addPersonalProduct(
        Cart $cart,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response {
        /** @var string|null $imageUrl */
        $imageUrl = $request->request->get(self::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER);

        if ($imageUrl === null) {
            throw new MissingRequestParameterException(self::PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER);
        }

        $lineItems = $request->request->get('lineItems', []);
        /** @var array|false $product */
        $product = reset($lineItems);
        if ($product === false) {
            throw new MissingRequestParameterException('lineItems');
        }

        $this->personalProductLineItemService->add($cart, $imageUrl, $product['id'], (int) $product['quantity'], $salesChannelContext);

        $this->addFlash('success', $this->trans('personalProduct.addToCart.success'));

        return $this->createActionResponse($request);
    }

    /**
     * @Route("/personal-product/{id}/personal-image", name="frontend.personal-product.get-image", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function getPersonalImage(
        string $id,
        SalesChannelContext $salesChannelContext
    ): Response {
        $url = $this->imageService->getRandomUrlByProductId($id, $salesChannelContext);

        return new JsonApiResponse(['url' => $url]);
    }
}
