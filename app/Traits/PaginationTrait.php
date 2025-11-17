<?php 

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait PaginationTrait {
    public function usePagination(ResourceCollection|JsonResource $data, LengthAwarePaginator|Paginator $paginated)
    {
        return [
            "data" => $data,
            "pagination" => [
                'total' => method_exists($paginated, 'total') ? $paginated->total() : null,
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => method_exists($paginated, 'lastPage') ? $paginated->lastPage() : null,
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ]
        ];
    }
}