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
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * GET /api/admin/dashboard/stats
     */
    public function stats(): void
    {
        $prodStmt = $this->db->query("SELECT COUNT(*) FROM products");
        $productsCount = (int)$prodStmt->fetchColumn();

        $catStmt = $this->db->query("SELECT COUNT(*) FROM categories");
        $categoriesCount = (int)$catStmt->fetchColumn();

        $this->success([
            "total_orders" => 142,
            "pending_orders" => 12,
            "approved_orders" => 130,
            "delivered_orders" => 115,
            "revenue" => 245000,
            "customers" => 84,
            "products" => $productsCount,
            "categories" => $categoriesCount,
            "monthly_sales" => 87000,
            "visitors" => 1250,
            "deltas" => [
                "total_orders" => 12.5,
                "revenue" => 8.2,
                "customers" => 15.1,
                "visitors" => -2.4
            ]
        ]);
    }

    /**
     * GET /api/admin/dashboard/revenue
     */
    public function revenue(): void
    {
        $data = [];
        $today = time();
        for ($i = 29; $i >= 0; $i--) {
            $ts = $today - ($i * 24 * 60 * 60);
            $label = date('d M', $ts);
            $seed = (int)date('d', $ts);
            $orders = ($seed % 5) + 1;
            $rev = $orders * 1500 + ($seed * 123) % 2000;
            $data[] = [
                "label" => $label,
                "revenue" => $rev,
                "orders" => $orders
            ];
        }

        $this->success($data);
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

        $orders = [
            [ "id" => 1001, "code" => "AS-1001", "customer_name" => "Rohan Mehta", "amount" => 1499, "status" => "delivered", "created_at" => date('Y-m-d H:i:s', time() - 3600) ],
            [ "id" => 1002, "code" => "AS-1002", "customer_name" => "Priya Sharma", "amount" => 2999, "status" => "approved", "created_at" => date('Y-m-d H:i:s', time() - 7200) ],
            [ "id" => 1003, "code" => "AS-1003", "customer_name" => "Amit Patel", "amount" => 899, "status" => "pending", "created_at" => date('Y-m-d H:i:s', time() - 12000) ],
            [ "id" => 1004, "code" => "AS-1004", "customer_name" => "Neha Gupta", "amount" => 4200, "status" => "shipped", "created_at" => date('Y-m-d H:i:s', time() - 15000) ],
            [ "id" => 1005, "code" => "AS-1005", "customer_name" => "Vikram Singh", "amount" => 1850, "status" => "pending", "created_at" => date('Y-m-d H:i:s', time() - 20000) ],
            [ "id" => 1006, "code" => "AS-1006", "customer_name" => "Suresh Kumar", "amount" => 3100, "status" => "delivered", "created_at" => date('Y-m-d H:i:s', time() - 25000) ]
        ];

        $this->success(array_slice($orders, 0, $limit));
    }

    /**
     * GET /api/admin/products/low-cost_price
     */
    public function lowStock(): void
    {
        $limit = (int)Request::query('limit', 6);

        $stmt = $this->db->query("SELECT id, name, sku FROM products LIMIT " . $limit);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            $data = [];
            foreach ($rows as $index => $row) {
                $data[] = [
                    "id" => (int)$row['id'],
                    "name" => $row['name'],
                    "sku" => $row['sku'],
                    "cost_price" => ($index % 3) + 1
                ];
            }
            $this->success($data);
        } else {
            $mock = [
                [ "id" => 1, "name" => "Sample Streetwear Tee", "sku" => "ST-TEE-01", "cost_price" => 3 ],
                [ "id" => 2, "name" => "Heavyweight Cargo Pants", "sku" => "ST-CRG-02", "cost_price" => 1 ]
            ];
            $this->success($mock);
        }
    }
}
