<?php
// File: app/Repositories/Interfaces/FeePaymentRepositoryInterface.php

namespace App\Repositories\Interfaces;

use App\Models\FeePayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FeePaymentRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?FeePayment;
    public function create(array $data): FeePayment;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
   
    // Payment specific methods
    public function getByStudent(int $studentId): Collection;
    public function getByMethod(string $method): Collection;
    public function getVerified(): Collection;
    public function getUnverified(): Collection;
    public function getByDateRange(string $startDate, string $endDate): Collection;
   
    // Statistics
    public function getPaymentStatistics(string $startDate, string $endDate): array;
    public function getDailyCollectionReport(string $date): array;
    public function getMonthlyCollectionReport(int $month, int $year): array;
}