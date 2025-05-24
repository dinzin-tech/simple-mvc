<?php
	
	namespace Core;
	
	class PaginationHelper
	{
		public static function createPaginationLinks($currentPage, $totalPages, $baseUrl) {
			$links          = [];
			$maxPagesToShow = 5; // Adjust as needed
			$pageKey        = '?page=';
			
			// Calculate start and end page numbers for the visible range
			$startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
			$endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
			
			// Generate previous link
			if ($currentPage > 1) {
				$links[] = '<a href="' . $baseUrl . $pageKey . ($currentPage - 1) . '">Previous</a>';
			}
			
			// Generate page number links
			for ($i = $startPage; $i <= $endPage; $i++) {
				$link = '<a href="' . $baseUrl . $pageKey . $i . '">' . $i . '</a>';
				$links[] = $i == $currentPage ? '<strong>' . $link . '</strong>' : $link;
			}
			
			// Generate next link
			if ($currentPage < $totalPages) {
				$links[] = '<a href="' . $baseUrl . $pageKey . ($currentPage + 1) . '">Next</a>';
			}
			
			return '<div class="paginate">'.implode(' ', $links).'</div>';
		}
	}