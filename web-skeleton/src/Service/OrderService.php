<?php


namespace App\Service;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderService
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var ProductService
     */
    private $productService;

    /**
     * OrderService constructor.
     * @param EntityManagerInterface $entityManager
     * @param ManagerRegistry $doctrine
     * @param ProductService $productService
     */
    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, ProductService $productService)
    {
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
        $this->productService = $productService;
    }

    /**
     * @return mixed
     */
    public function allOrders()
    {
        return $this->doctrine->getManager()->getRepository(Order::class)->findAll();
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $user
     * @return mixed
     */
    public function getMyOrders(UserInterface $user)
    {
        return $this->doctrine->getManager()->getRepository(Order::class)->findByUser($user);
    }

    /**
     * @param User $user
     * @param Product $product
     * @param int $quantity
     * @param $shipping_address
     * @return Order
     * @throws Exception
     */
    public function newOrder(UserInterface $user, Product $product, int $quantity, $shipping_address)
    {

        if ($product->getQuantity() > $quantity) {

            $order = new Order();

            $order->setUser($user);
            $order->setProduct($product);
            $order->setOrderCode(rand(10000, 99999));
            $order->setQuantity($quantity);
            $order->setPrice($quantity * $product->getPrice());
            $order->setOrderDate(new \DateTime('now'));
            $order->setShippingAddress($shipping_address);
            $order->setAddress($shipping_address);
            $order->setShippingDate(new \DateTime('tomorrow'));

            $this->entityManager->persist($order);
            $this->entityManager->flush();


            return $order;

        }
        throw new Exception("You can't order more of the product stock");


    }


    /**
     * @param $id
     * @return mixed
     */
    public function orderDetail($id)
    {
        return $this->doctrine->getManager()->getRepository(Order::class)->find($id);
    }

    /**
     * @param $order
     * @param $updateData
     * @return Order
     * @throws Exception
     */
    public function orderUpdate(Order $order, $updateData)
    {

        $product_id = ($updateData->product_id > 0) ? $updateData->product_id : $order->getProduct()->getId();

        $product = $this->productService->getProduct($product_id);

        // Stock status check
        if ($updateData->quantity > $product->getQuantity()) {
            throw new Exception("You can't order more of the product stock");
        }


        // [optional] Product update
        // Product control
        if ($updateData->product_id > 0) {

            // [optional]Quantity update
            if ($updateData->quantity) {
                $order->setQuantity($updateData->quantity);
            }

            $order->setProduct($product);
            $order->setPrice($order->getQuantity() * $product->getPrice());
        }

        if ($updateData->quantity) {
            $order->setPrice($updateData->quantity * $order->getPrice());
            $order->setQuantity($updateData->quantity);
        }

        // Shipping address update
        if ($updateData->shipping_address) {
            $order->setShippingAddress($updateData->shipping_address);
        }

        $this->entityManager->flush();

        return $order;
    }


}