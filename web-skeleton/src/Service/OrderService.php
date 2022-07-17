<?php


namespace App\Service;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use OrderUpdateDTO;

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
     * @param ProductService $productService
     */
    public function __construct(EntityManagerInterface $entityManager, ProductService $productService)
    {
        $this->entityManager = $entityManager;
        $this->productService = $productService;
    }

    /**
     * @return mixed
     */
    public function allOrders()
    {
        return $this->entityManager->getRepository(Order::class)->findAll();
    }

    /**
     * @param UserInterface|null $user
     * @return mixed
     */
    public function getOrdersByUser(UserInterface $user)
    {
        return $this->entityManager->getRepository(Order::class)->findByUser($user);
    }

    /**
     * @param UserInterface $user
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
        return $this->entityManager->getRepository(Order::class)->find($id);
    }

    /**
     * @param Order $order
     * @param $product_id
     * @param $quantity
     * @param $shipping_address
     * @return OrderUpdateDTO
     * @throws Exception
     */
    public function orderUpdate(Order $order, $product_id, $quantity, $shipping_address)
    {

        $updateData = new OrderUpdateDTO();
        $updateData->setProductId($product_id);
        $updateData->setQuantity($quantity);
        $updateData->setShippingAddress($shipping_address);


        $product_id = ($updateData->getProductId() > 0) ? $updateData->getProductId() : $order->getProduct()->getId();

        $product = $this->productService->getProduct($product_id);

        // Stock status check
        if ($updateData->getQuantity() > $product->getQuantity()) {
            throw new Exception("You can't order more of the product stock");
        }


        // [optional] Product update
        // Product control
        if ($updateData->getProductId() > 0) {

            // [optional]Quantity update
            if ($updateData->getQuantity()) {
                $order->setQuantity($updateData->getQuantity());
            }

            $order->setProduct($product);
            $order->setPrice($order->getQuantity() * $product->getPrice());
        }

        if ($updateData->getQuantity()) {
            $order->setPrice($updateData->getQuantity() * $order->getPrice());
            $order->setQuantity($updateData->getQuantity());
        }

        // Shipping address update
        if ($updateData->getShippingAddress()) {
            $order->setShippingAddress($updateData->getShippingAddress());
        }

        $this->entityManager->flush();

        return $updateData;
    }


}