<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use Exception;
use PDO;

class OrderService
{
    private OrderRepository $repository;
    private CustomerService $customerService;

    public function __construct()
    {
        $this->repository = new OrderRepository();
        $this->customerService = new CustomerService();
    }

    public function getAllPaginated(int $page, int $perPage, ?string $search = null, ?string $paymentStatus = null, ?string $orderStatus = null): array
    {
        return $this->repository->findAllPaginated($page, $perPage, $search, $paymentStatus, $orderStatus);
    }

    public function getById(int $id): ?Order
    {
        return $this->repository->findById($id);
    }

    public function createOrder(array $data): Order
    {
        $shipping = $data['shipping'] ?? [];
        $itemsData = $data['items'] ?? [];

        if (empty($itemsData)) {
            throw new Exception("Cannot place an order with empty items list.");
        }

        if (empty($shipping['name']) || empty($shipping['email']) || empty($shipping['phone'])) {
            throw new Exception("Shipping details (name, email, phone) are required.");
        }

        $db = \App\Core\Database::connection();
        $db->beginTransaction();

        try {
            $productRepo = new \App\Repositories\ProductRepository();

            // 1. Validate ALL products and stock levels before modifying any database tables!
            $validatedItems = [];
            $totalAmount = 0.0;

            foreach ($itemsData as $item) {
                $product = null;
                if (!empty($item['handle'])) {
                    $product = $productRepo->findBySlug($item['handle']);
                }

                if (!$product) {
                    throw new Exception("Product '" . ($item['handle'] ?? 'Unknown') . "' not found in database. Please clear your cart and add active products.");
                }

                $qty = (int)($item['quantity'] ?? 1);
                if ($qty <= 0) {
                    throw new Exception("Invalid quantity for product '" . $product->getName() . "'.");
                }

                // Check stock levels in `inventory` table
                $stmt = $db->prepare("SELECT * FROM inventory WHERE product_id = :product_id LIMIT 1");
                $stmt->execute([':product_id' => $product->getId()]);
                $inv = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$inv) {
                    // Initialize default inventory for development if not exists (starts with 100 in stock)
                    $insertStmt = $db->prepare("INSERT INTO inventory (product_id, quantity, warehouse) VALUES (:product_id, 100, 'Main Warehouse')");
                    $insertStmt->execute([':product_id' => $product->getId()]);
                    $available = 100;
                } else {
                    $available = (int)$inv['quantity'];
                }

                if ($qty > $available) {
                    throw new Exception("Product '" . $product->getName() . "' does not have enough stock. Available: " . $available . ", Requested: " . $qty);
                }

                $validatedItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'size' => $item['size'] ?? null
                ];

                $totalAmount += (float)$product->getSellingPrice() * $qty;
            }

            // 2. Record customer stats
            $customer = $this->customerService->recordOrder(
                $shipping['name'],
                $shipping['email'],
                $shipping['phone'],
                $totalAmount
            );

            // 3. Build & Create Order record
            $order = new Order();
            $order->setCustomerId($customer->getId());
            $order->setOrderNumber('ORD-' . strtoupper(dechex(time())) . '-' . rand(1000, 9999));
            $order->setTotalAmount($totalAmount);
            $order->setPaymentStatus($data['payment_status'] ?? 'PENDING');
            $order->setOrderStatus($data['order_status'] ?? 'PENDING');
            $order->setShippingName($shipping['name']);
            $order->setShippingEmail($shipping['email']);
            $order->setShippingPhone($shipping['phone']);
            $order->setShippingAddress($shipping['address'] ?? '');
            $order->setShippingCity($shipping['city'] ?? '');
            $order->setShippingPincode($shipping['pincode'] ?? '');

            $orderId = $this->repository->create($order);
            $order->setId($orderId);

            // 4. Save line items and Decrement inventory stock
            $orderItems = [];
            foreach ($validatedItems as $vItem) {
                $product = $vItem['product'];
                $qty = $vItem['qty'];
                $size = $vItem['size'];

                // Decrement inventory stock
                $updateStmt = $db->prepare("UPDATE inventory SET quantity = quantity - :qty WHERE product_id = :product_id");
                $updateStmt->execute([
                    ':qty' => $qty,
                    ':product_id' => $product->getId()
                ]);

                // Create OrderItem
                $orderItem = new OrderItem();
                $orderItem->setOrderId($orderId);
                $orderItem->setProductId($product->getId());
                $orderItem->setProductName($product->getName());
                $orderItem->setPrice((float)$product->getSellingPrice());
                $orderItem->setQuantity($qty);
                $orderItem->setSize($size);

                $itemId = $this->repository->createItem($orderItem);
                $orderItem->setId($itemId);
                $orderItems[] = $orderItem;
            }

            $order->setItems($orderItems);
            $db->commit();
            return $order;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus(int $id, string $orderStatus, string $paymentStatus): bool
    {
        $db = \App\Core\Database::connection();
        $db->beginTransaction();

        try {
            $order = $this->repository->findById($id);
            if (!$order) {
                throw new Exception("Order not found.");
            }

            $oldOrderStatus = $order->getOrderStatus();
            $newStatusUpper = strtoupper($orderStatus);
            $oldStatusUpper = strtoupper($oldOrderStatus);

            // If changing to CANCELLED or REFUNDED, restore stock
            $isCancelling = ($newStatusUpper === 'CANCELLED' || $newStatusUpper === 'REFUNDED');
            $wasCancelledAlready = ($oldStatusUpper === 'CANCELLED' || $oldStatusUpper === 'REFUNDED');

            if ($isCancelling && !$wasCancelledAlready) {
                // Restore inventory stock for all items in the order
                $items = $this->repository->findItemsByOrderId($id);
                foreach ($items as $item) {
                    $updateStmt = $db->prepare("UPDATE inventory SET quantity = quantity + :qty WHERE product_id = :product_id");
                    $updateStmt->execute([
                        ':qty' => $item->getQuantity(),
                        ':product_id' => $item->getProductId()
                    ]);
                }
            } 
            // If changing from CANCELLED/REFUNDED back to active, deduct stock (with verification)
            elseif (!$isCancelling && $wasCancelledAlready) {
                $items = $this->repository->findItemsByOrderId($id);
                foreach ($items as $item) {
                    // Check stock
                    $stmt = $db->prepare("SELECT quantity FROM inventory WHERE product_id = :product_id LIMIT 1");
                    $stmt->execute([':product_id' => $item->getProductId()]);
                    $available = (int)$stmt->fetchColumn();

                    if ($item->getQuantity() > $available) {
                        throw new Exception("Cannot reactivate order. Product '" . $item->getProductName() . "' does not have enough stock (Available: " . $available . ").");
                    }

                    $updateStmt = $db->prepare("UPDATE inventory SET quantity = quantity - :qty WHERE product_id = :product_id");
                    $updateStmt->execute([
                        ':qty' => $item->getQuantity(),
                        ':product_id' => $item->getProductId()
                    ]);
                }
            }

            $success = $this->repository->updateStatus($id, $orderStatus, $paymentStatus);
            if ($success) {
                $db->commit();
                return true;
            } else {
                $db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function getCustomerOrders(int $customerId): array
    {
        return $this->repository->findByCustomerId($customerId);
    }
}
