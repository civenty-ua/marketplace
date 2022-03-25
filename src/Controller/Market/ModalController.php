<?php

namespace App\Controller\Market;

use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityProduct;
use App\Entity\Market\Notification\BidOffer;
use App\Entity\Market\Notification\PriceOfferNotification;
use App\Entity\User;
use App\Form\Market\BidOfferType;
use App\Form\Market\OfferPriceFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\Notification\BidOfferNotificationSender;
use App\Service\Notification\PriceOfferNotificationSender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class ModalController extends AbstractController
{
    private PriceOfferNotificationSender $priceOfferNotificationSender;
    private BidOfferNotificationSender $bidOfferNotificationSender;
    private TranslatorInterface $translator;

    public function __construct(
        PriceOfferNotificationSender $priceOfferNotificationSender,
        BidOfferNotificationSender   $bidOfferNotificationSender,
        TranslatorInterface          $translator
    )
    {
        $this->priceOfferNotificationSender = $priceOfferNotificationSender;
        $this->bidOfferNotificationSender = $bidOfferNotificationSender;
        $this->translator = $translator;
    }

    /**
     * @Route("/marketplace/bid-offer-ajax/{itemId}", name="bid_offer_form")
     */
    public function bidOffer(Request $request, $itemId, ?UserInterface $user): JsonResponse
    {
        /** @var User|null $user */
        if (!$user) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'redirectUrl' => $this->generateUrl('login')
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->isXmlHttpRequest()) {

            $notification = new BidOffer();
            $comodity = $this->getDoctrine()->getRepository(Commodity::class)->find($itemId);

            $notification->setName($this->getUser()->getName());
            $actionUrl = $this->generateUrl('bid_offer_form', ['itemId' => $comodity->getId()]);

            $form = $this->createForm(
                BidOfferType::class,
                $notification,
                [
                    'attr' => [
                        'data-market-form' => $actionUrl
                    ]
                ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $data = [
                        'name' => $this->getUser()->getUsername(),
                        'phone' => $notification->getPhone(),
                        'price' => $comodity->getPrice(),
                        'message' => $notification->getMessage(),
                        'commodity' => $comodity,
                        'receiver' => $comodity->getUser(),
                        'sender' => $this->getUser(),
                        'title' => $this->setTitleByCommodityBidType($comodity)
                    ];
                    $this->bidOfferNotificationSender->sendSingleNotification($data);
                    $this->addFlash('success', $this->translator->trans('form_offer_price.bid_success'));
                    return new JsonResponse([
                        'status' => 'OK',
                    ], Response::HTTP_OK);
                } else {
                    return $this->renderForm('market/form/order-form.html.twig', $form, $comodity);
                }
            }
            return $this->renderForm('market/form/order-form.html.twig', $form, $comodity);
        } else {
            return new JsonResponse('Not Ajax', Response::HTTP_BAD_REQUEST);
        }
    }

    private function renderForm($template, $form, $comodity)
    {
        return new JsonResponse([
            'form' => $this->render(
                $template,
                [
                    'form' => $form->createView(),
                    'comodity' => $comodity
                ]
            )->getContent(),
            'confirmBtn' => $this->translator->trans('form_offer_price.confirm'),
            'cancelBtn' => $this->translator->trans('form_offer_price.cancel'),
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/marketplace/offer-price-ajax/{itemId}", name="offer_price_form")
     */
    public function offerPrice(Request $request, $itemId, ?UserInterface $user): JsonResponse
    {
        /** @var User|null $user */
        if (!$user) {
            return new JsonResponse([
                'redirectUrl' => $this->generateUrl('login')
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->isXmlHttpRequest()) {

            $notification = new PriceOfferNotification();
            /** @var Commodity $comodity */
            $comodity = $this->getDoctrine()->getRepository(Commodity::class)->find($itemId);
            $notification->setName($this->getUser()->getName());

            $actionUrl = $this->generateUrl('offer_price_form', ['itemId' => $comodity->getId()]);
            $form = $this->createForm(
                OfferPriceFormType::class,
                $notification,
                [
                    'attr' => [
                        'data-market-form' => $actionUrl
                    ]
                ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $data = [
                        'name' => $this->getUser()->getUsername(),
                        'phone' => $notification->getPhone(),
                        'price' => $notification->getPrice(),
                        'message' => $notification->getMessage(),
                        'commodity' => $comodity,
                        'receiver' => $comodity->getUser(),
                        'sender' => $this->getUser(),
                        'title' => "Пропозиція ціни щодо {$comodity->getTitle()}"
                    ];
                    $this->priceOfferNotificationSender->sendSingleNotification($data);
                    $this->addFlash('success', $this->translator->trans('form_offer_price.success'));
                    return new JsonResponse([
                        'status' => 'OK',
                    ], Response::HTTP_OK);
                } else {
                    return $this->renderForm('market/form/offer-price-form.html.twig', $form, $comodity);
                }
            }
            return $this->renderForm('market/form/offer-price-form.html.twig', $form, $comodity);
        } else {
            return new JsonResponse('Not Ajax', Response::HTTP_BAD_REQUEST);
        }
    }

    private function setTitleByCommodityBidType(Commodity $commodity): ?string
    {
        $title = null;
        switch ($commodity->getCommodityType()) {
            case Commodity::TYPE_PRODUCT://todo by sell buy and service order
                switch ($commodity->getType()) {
                    case CommodityProduct::TYPE_BUY:
                        $title = "Запит на продаж вам {$commodity->getTitle()}";
                        break;
                    case CommodityProduct::TYPE_SELL:
                        $title = "Запит на купівлю {$commodity->getTitle()}";
                        break;
                }
                break;
            case Commodity::TYPE_SERVICE:
                $title = "Замовлення послуги {$commodity->getTitle()}";
                break;
            case Commodity::TYPE_KIT:
                $title = "Замовлення спільної пропозиції {$commodity->getTitle()}";
                break;
        }
        return $title;
    }
}
