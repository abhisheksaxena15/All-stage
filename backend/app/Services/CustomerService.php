<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;

class CustomerService
{
    private CustomerRepository $repository;

    public function __construct()
    {
        $this->repository = new CustomerRepository();
    }

    public function getAllPaginated(int $page, int $perPage, ?string $search = null): array
    {
        return $this->repository->findAllPaginated($page, $perPage, $search);
    }

    /**
     * Create or update customer stats when placing an order
     */
    public function recordOrder(string $name, string $email, ?string $phone, float $amount): Customer
    {
        $customer = $this->repository->findByEmail($email);

        if ($customer) {
            $customer->setName($name);
            if ($phone) {
                $customer->setPhone($phone);
            }
            $customer->setOrdersCount($customer->getOrdersCount() + 1);
            $customer->setTotalSpent($customer->getTotalSpent() + $amount);
            $this->repository->update($customer);
        } else {
            $customer = new Customer();
            $customer->setName($name);
            $customer->setEmail($email);
            $customer->setPhone($phone);
            $customer->setOrdersCount(1);
            $customer->setTotalSpent($amount);
            $id = $this->repository->create($customer);
            $customer->setId($id);
        }

        return $customer;
    }

    /**
     * Create or retrieve customer profile for OTP login/register
     */
    public function loginOrRegister(string $email, string $name, ?string $phone): Customer
    {
        $customer = $this->repository->findByEmail($email);

        if ($customer) {
            $updated = false;
            if (!empty($name) && $customer->getName() !== $name) {
                $customer->setName($name);
                $updated = true;
            }
            if (!empty($phone) && $customer->getPhone() !== $phone) {
                $customer->setPhone($phone);
                $updated = true;
            }
            if ($updated) {
                $this->repository->update($customer);
            }
        } else {
            $customer = new Customer();
            $customer->setName($name ?: 'Valued Customer');
            $customer->setEmail($email);
            $customer->setPhone($phone);
            $customer->setOrdersCount(0);
            $customer->setTotalSpent(0.00);
            $id = $this->repository->create($customer);
            $customer->setId($id);
        }

        return $customer;
    }
}
