<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\OrderService;
use Exception;

class OrderController extends BaseController
{
    private OrderService $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    /**
     * GET /api/admin/orders
     */
    public function index(): void
    {
        $limit = Request::query('limit');
        if ($limit !== null && $limit !== '') {
            try {
                $perPage = (int)$limit;
                $result = $this->service->getAllPaginated(1, $perPage);
                
                $formatted = array_map(function($order) {
                    return [
                        'id' => $order->getId(),
                        'code' => $order->getOrderNumber(),
                        'customer_name' => $order->getCustomerName() ?? $order->getShippingName(),
                        'amount' => $order->getTotalAmount(),
                        'status' => strtolower($order->getOrderStatus()),
                        'created_at' => $order->getCreatedAt()
                    ];
                }, $result['data']);

                $this->success($formatted);
            } catch (Exception $e) {
                $this->error($e->getMessage(), 400);
            }
            return;
        }

        $page = (int)Request::query('page', 1);
        $perPage = (int)Request::query('per_page', 25);
        $search = Request::query('search');
        $paymentStatus = Request::query('payment_status');
        $orderStatus = Request::query('order_status');

        try {
            $result = $this->service->getAllPaginated($page, $perPage, $search, $paymentStatus, $orderStatus);

            $formatted = array_map(
                fn($order) => $order->toArray(),
                $result['data']
            );

            $this->success([
                'data' => $formatted,
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/admin/orders/{id}
     */
    public function show(): void
    {
        $id = (int)Request::param('id');

        try {
            $order = $this->service->getById($id);
            if (!$order) {
                $this->error("Order not found.", 404);
                return;
            }
            $this->success($order->toArray());
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/admin/orders
     */
    public function store(): void
    {
        $data = Request::body();

        try {
            $order = $this->service->createOrder($data);

            /*
            // ==========================================
            // FUTURE PAYMENT GATEWAY INTEGRATION (BACKEND)
            // ==========================================
            // When order is created, generate a gateway order ID via Razorpay/Stripe API:
            // 
            // 1. Initialize your payment gateway client (e.g. Razorpay SDK):
            //    $api = new \Razorpay\Api\Api($_ENV['RAZORPAY_KEY'], $_ENV['RAZORPAY_SECRET']);
            //
            // 2. Create the gateway order payload:
            //    $gatewayOrder = $api->order->create([
            //        'receipt' => 'REC-' . $order->getOrderNumber(),
            //        'amount' => $order->getTotalAmount() * 100, // Amount in paise
            //        'currency' => 'INR'
            //    ]);
            //
            // 3. Save the returned gateway order ID to the order record in database:
            //    $order->setGatewayOrderId($gatewayOrder['id']);
            //    $orderRepository->updateGatewayId($order->getId(), $gatewayOrder['id']);
            //
            // 4. Return the gateway order ID to the frontend to launch checkout modal:
            //    $responseArray = $order->toArray();
            //    $responseArray['gateway_order_id'] = $gatewayOrder['id'];
            //    $this->success($responseArray, "Gateway order generated successfully.", 201);
            //    return;
            */

            $this->success($order->toArray(), "Order placed successfully.", 201);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/orders/verify
     * 
     * FUTURE PAYMENT SIGNATURE VERIFICATION ENDPOINT
     */
    /*
    public function verifyPayment(): void
    {
        $data = Request::body();
        $orderId = (int)($data['order_id'] ?? 0);
        $razorpayOrderId = $data['razorpay_order_id'] ?? '';
        $razorpayPaymentId = $data['razorpay_payment_id'] ?? '';
        $razorpaySignature = $data['razorpay_signature'] ?? '';

        try {
            // Verify HMAC signature:
            // $generated_signature = hash_hmac(
            //     'sha256', 
            //     $razorpayOrderId . "|" . $razorpayPaymentId, 
            //     $_ENV['RAZORPAY_WEBHOOK_SECRET']
            // );
            // 
            // if ($generated_signature === $razorpaySignature) {
            //     // Payment verified successfully! Update order status to paid in DB:
            //     $orderService->updateOrderStatus($orderId, 'CONFIRMED', 'PAID');
            //     $this->success([], "Payment verified successfully.");
            // } else {
            //     $this->error("Invalid signature hash.", 400);
            // }
        } catch (Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
    */

    /**
     * PUT /api/admin/orders/{id}
     */
    public function update(): void
    {
        $id = (int)Request::param('id');
        $data = Request::body();

        try {
            $orderStatus = $data['order_status'] ?? 'PENDING';
            $paymentStatus = $data['payment_status'] ?? 'PENDING';
            $success = $this->service->updateOrderStatus($id, $orderStatus, $paymentStatus);
            if ($success) {
                $this->success([], "Order status updated.");
            } else {
                $this->error("Failed to update order status.", 400);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
