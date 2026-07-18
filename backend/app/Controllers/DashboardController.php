<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Request;
use App\Core\Database;
use PDO;

class DashboardController extends BaseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * GET /api/admin/dashboard/stats
     */
    public function stats(): void
    {
        try {
            // Products count
            $prodStmt = $this->db->query("SELECT COUNT(*) FROM products");
            $productsCount = (int)$prodStmt->fetchColumn();

            // Categories count
            $catStmt = $this->db->query("SELECT COUNT(*) FROM categories");
            $categoriesCount = (int)$catStmt->fetchColumn();

            // Total Customers
            $custStmt = $this->db->query("SELECT COUNT(*) FROM customers");
            $customersCount = (int)$custStmt->fetchColumn();

            // Total Orders
            $ordersStmt = $this->db->query("SELECT COUNT(*) FROM orders");
            $totalOrders = (int)$ordersStmt->fetchColumn();

            // Pending Orders
            $pendingStmt = $this->db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'PENDING'");
            $pendingOrders = (int)$pendingStmt->fetchColumn();

            // Approved Orders (Shipped or Completed)
            $approvedStmt = $this->db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('SHIPPED', 'COMPLETED', 'APPROVED', 'PROCESSING')");
            $approvedOrders = (int)$approvedStmt->fetchColumn();

            // Delivered Orders (Completed)
            $deliveredStmt = $this->db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'COMPLETED'");
            $deliveredOrders = (int)$deliveredStmt->fetchColumn();

            // Revenue
            $revStmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0.00) FROM orders WHERE order_status != 'CANCELLED'");
            $revenue = (float)$revStmt->fetchColumn();

            // Monthly Sales (current calendar month)
            $monthlySalesStmt = $this->db->query("
                SELECT COALESCE(SUM(total_amount), 0.00) 
                FROM orders 
                WHERE order_status != 'CANCELLED' 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE())
            ");
            $monthlySales = (float)$monthlySalesStmt->fetchColumn();

            // Unique visitors (by IP)
            $visitorsStmt = $this->db->query("SELECT COUNT(DISTINCT ip_address) FROM visits");
            $visitors = (int)$visitorsStmt->fetchColumn();

            // Compute previous month values for deltas
            $prevMonthOrdersStmt = $this->db->query("
                SELECT COUNT(*) FROM orders 
                WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $prevMonthOrders = (int)$prevMonthOrdersStmt->fetchColumn();

            $prevMonthRevStmt = $this->db->query("
                SELECT COALESCE(SUM(total_amount), 0.00) FROM orders 
                WHERE order_status != 'CANCELLED'
                  AND created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $prevMonthRev = (float)$prevMonthRevStmt->fetchColumn();

            $prevMonthCustStmt = $this->db->query("
                SELECT COUNT(*) FROM customers 
                WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $prevMonthCust = (int)$prevMonthCustStmt->fetchColumn();

            $prevMonthVisStmt = $this->db->query("
                SELECT COUNT(DISTINCT ip_address) FROM visits 
                WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)
                  AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ");
            $prevMonthVis = (int)$prevMonthVisStmt->fetchColumn();

            $deltaOrders = $prevMonthOrders == 0 ? ($totalOrders > 0 ? 100.0 : 0.0) : round((($totalOrders - $prevMonthOrders) / $prevMonthOrders) * 100, 1);
            $deltaRev = $prevMonthRev == 0 ? ($revenue > 0 ? 100.0 : 0.0) : round((($revenue - $prevMonthRev) / $prevMonthRev) * 100, 1);
            $deltaCust = $prevMonthCust == 0 ? ($customersCount > 0 ? 100.0 : 0.0) : round((($customersCount - $prevMonthCust) / $prevMonthCust) * 100, 1);
            $deltaVis = $prevMonthVis == 0 ? ($visitors > 0 ? 100.0 : 0.0) : round((($visitors - $prevMonthVis) / $prevMonthVis) * 100, 1);

            $this->success([
                "total_orders" => $totalOrders,
                "pending_orders" => $pendingOrders,
                "approved_orders" => $approvedOrders,
                "delivered_orders" => $deliveredOrders,
                "revenue" => $revenue,
                "customers" => $customersCount,
                "products" => $productsCount,
                "categories" => $categoriesCount,
                "monthly_sales" => $monthlySales,
                "visitors" => $visitors,
                "deltas" => [
                    "total_orders" => $deltaOrders,
                    "revenue" => $deltaRev,
                    "customers" => $deltaCust,
                    "visitors" => $deltaVis
                ]
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/admin/dashboard/revenue
     */
    public function revenue(): void
    {
        try {
            $sql = "
                SELECT DATE(created_at) as order_date, 
                       COUNT(id) as orders_count, 
                       SUM(total_amount) as daily_revenue
                FROM orders
                WHERE order_status != 'CANCELLED' 
                  AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 29 DAY)
                GROUP BY DATE(created_at)
            ";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $indexed = [];
            foreach ($rows as $row) {
                $indexed[$row['order_date']] = [
                    'orders' => (int)$row['orders_count'],
                    'revenue' => (float)$row['daily_revenue']
                ];
            }

            $data = [];
            $today = time();
            for ($i = 29; $i >= 0; $i--) {
                $ts = $today - ($i * 24 * 60 * 60);
                $dateKey = date('Y-m-d', $ts);
                $label = date('d M', $ts);

                if (isset($indexed[$dateKey])) {
                    $data[] = [
                        "label" => $label,
                        "revenue" => $indexed[$dateKey]['revenue'],
                        "orders" => $indexed[$dateKey]['orders']
                    ];
                } else {
                    $data[] = [
                        "label" => $label,
                        "revenue" => 0.00,
                        "orders" => 0
                    ];
                }
            }

            $this->success($data);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/admin/dashboard/categories
     */
    public function categories(): void
    {
        $sql = "
            SELECT c.name, COUNT(p.id) as value
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
        ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array_map(function($row) {
            return [
                "name" => $row['name'],
                "value" => (int)$row['value']
            ];
        }, $rows);

        $hasValue = false;
        foreach ($data as $d) {
            if ($d['value'] > 0) {
                $hasValue = true;
                break;
            }
        }
        if ($hasValue) {
            $data = array_values(array_filter($data, fn($d) => $d['value'] > 0));
        }

        $this->success($data);
    }

    /**
     * GET /api/admin/orders
     */
    public function recentOrders(): void
    {
        $limit = (int)Request::query('limit', 6);
        try {
            $stmt = $this->db->prepare("
                SELECT id, order_number as code, shipping_name as customer_name, total_amount as amount, LOWER(order_status) as status, created_at 
                FROM orders 
                ORDER BY id DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->success($rows);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/admin/products/low-cost_price
     */
    public function lowStock(): void
    {
        $limit = (int)Request::query('limit', 6);

        try {
            $sql = "
                SELECT p.id, p.name, p.sku, COALESCE(SUM(i.quantity), 0) as stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                GROUP BY p.id
                ORDER BY stock ASC
                LIMIT :limit
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    "id" => (int)$row['id'],
                    "name" => $row['name'],
                    "sku" => $row['sku'],
                    "cost_price" => (int)$row['stock']
                ];
            }
            $this->success($data);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/visit
     */
    public function logVisit(): void
    {
        $data = Request::body();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $url = $data['url'] ?? '/';

        try {
            $stmt = $this->db->prepare("INSERT INTO visits (ip_address, page_url) VALUES (:ip_address, :page_url)");
            $stmt->execute([
                ':ip_address' => $ip,
                ':page_url' => $url
            ]);
            Response::success([], "Visit logged");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
