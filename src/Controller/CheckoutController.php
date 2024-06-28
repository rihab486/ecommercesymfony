<?php

namespace App\Controller;
use App\Services\CartService;
use App\Entity\Order;
use App\Entity\OrderDetails;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\AddressRepository;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;



class CheckoutController extends AbstractController
{

    public function __construct(
        private CartService $cartService,
        private RequestStack $requestStack,
        EntityManagerInterface $em

    ) {
        $this->cartService = $cartService;
        $this->session = $requestStack->getSession();
        $this->em = $em;

    }

    #[Route('/checkout', name: 'app_checkout')]
    public function index(AddressRepository $addressRepository ,StripeService $stripeservice): Response
    {
        
        $cart = $this->cartService->getCartDetails();

        if(!(count($cart["items"]))){
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();
        if(!$user){
            $this->session->set("next","app_checkout");
           return $this->redirectToRoute("app_login");
        }
        $addresses = $addressRepository->findByUser($user);
        $cart_json =json_encode($cart);

        $orderId = $this->createOrder($cart);
     
        //dd($orderId);
 
        $publickey =$stripeservice->getPublicKey();

        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'cart'=>$cart,
            'orderId' => $orderId,
            'cart_json'=>$cart_json,
            'public_key'=>$publickey,
            'addresses'=>$addresses,
        ]);
    }

    public function createOrder($cart){
        $user = $this->getUser();
        $order= new Order();
        $order  ->setClientName($user->getFullName())
                ->setBillingAddress("")
                ->setShippingAddress("")
                ->setOrderCost($cart["sub_total"])
                ->setTaxe($cart["taxe"])
                ->setOrderCostTtc($cart["sub_total_with_carrier"])
                ->setCarrierName($cart['carrier']['name'])
                ->setClientAddress("")
                ->setCarrierPrice($cart['carrier']['price'])
                ->setCarrierId($cart['carrier']['id'])
                ->setQuantity($cart['quantity'])
                 ->setIsPaid(false)
                 ->setStatus("En cours")
        ;
        $this->em->persist($order);

        foreach ($cart["items"] as $key => $item) {
            $product = $item["product"];
       //   dd($product);

            $orderDetails = new OrderDetails();
            $orderDetails->setProductName($product['name'])
                         ->setProductDescription($product['slug'])
                         ->setProductSoldePrice($product['soldePrice'])
                         ->setProductRegularPrice($product['regularPrice'])
                         ->setQuantity($item['quantity'])
                         ->setSubTotal($item['sub_total'])
                         ->setTaxe($item['taxe'])
                         ->setMyOrder($order)
            ;
            $this->em->persist($orderDetails);
        }

        $this->em->flush();

        return $order->getId();

    }

   
}
