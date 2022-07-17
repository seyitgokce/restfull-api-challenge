<?php


namespace App\Controller;


use App\Entity\Order;
use App\Service\OrderService;
use App\Service\ProductService;
use App\Service\UserService;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class OrderController extends AbstractController
{

    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var ProductService
     */
    private $productService;

    public function __construct(OrderService $orderService, ProductService $productService)
    {

        $this->orderService = $orderService;
        $this->productService = $productService;
    }


    /**
     * @Route("/api/orders", name="post_order", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createOrder(Request $request)
    {
        $product_id = (int)$request->get('product_id');
        $quantity = (int)$request->get('quantity');
        $shipping_address = $request->get('shipping_address');

        // Basic request validation
        if ($product_id < 1 || $quantity < 1 || !$shipping_address) {
            // throw new ValidationException('Prooduct id, quantity and shipping address cannot be empty');
        }

        $product = $this->productService->getProduct($product_id);
        $user = $this->getUser();

        $order = $this->orderService->newOrder($user, $product, rand(1, 50), $shipping_address);


        return new JsonResponse(['success' => 'Order successfully created', 'order_code' => $order->getOrderCode()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/orders/{id}", name="update_order", methods={"PUT"}, requirements={"id"="\d+"})
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function updateOrder(Request $request, int $id)
    {

        $order = $this->orderService->orderDetail($id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $shipping_date = $order->getOrderDate();
        $now = new \DateTime('now');
        if ($shipping_date > $now) {
            throw new HttpException(400, 'Product shipped. The order cannot be updated.');
        }

        $product_id = (int)$request->get('product_id');
        $quantity = (int)$request->get('quantity');
        $shipping_address = $request->get('shipping_address');

        $updateData = $this->orderService->orderUpdate($order, $product_id, $quantity, $shipping_address);

        return new JsonResponse($updateData);
    }

    /**
     * @Route("/api/orders", name="get_orders", methods={"GET"})
     */
    public function index()
    {
        $orders = $this->orderService->allOrders();

        return new JsonResponse($orders);
    }

    /**
     * @Route("/api/orders/my", name="get_my_orders", methods={"GET"})
     */
    public function myOrders()
    {
        $user = $this->getUser();

        return new JsonResponse($this->orderService->getOrdersByUser($user));
    }


    /**
     * @Route("/api/orders/{id}", name="get_order", methods={"GET"}, requirements={"id"="\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function orderDetail(int $id)
    {

        $order = $this->orderService->orderDetail($id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return new JsonResponse($order);
    }

}