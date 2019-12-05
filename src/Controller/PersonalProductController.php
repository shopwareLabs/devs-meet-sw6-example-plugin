<?php declare(strict_types=1);

namespace SwagPersonalProduct\Controller;

use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use SwagPersonalProduct\Service\ImageService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class PersonalProductController extends StorefrontController
{
    public const PERSONAL_PRODUCT_REQUEST_IMAGE_URL_PARAMETER = 'personal-product-image-url';

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(
        ImageService $imageService
    ) {
        $this->imageService = $imageService;
    }

    /**
     * @Route("/personal-product/{id}/personal-image", name="frontend.personal-product.get-image", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function getPersonalImage(string $id, RequestDataBag $request, SalesChannelContext $salesChannelContext): Response
    {
        $image = $this->imageService->getImageUrl($id, $request->get('url'), $salesChannelContext);

        return new JsonApiResponse(['url' => $image->getUrl(), 'id' => $image->getId()]);
    }

    /**
     * @Route("/personal-product/{id}/personal-image", name="frontend.personal-product.random-image", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function getRandomImage(
        string $id,
        SalesChannelContext $salesChannelContext
    ): Response {
        $image = $this->imageService->getRandomPersonalImageByProductId($id, $salesChannelContext);

        return new JsonApiResponse(['url' => $image->getUrl(), 'id' => $image->getId()]);
    }
}
