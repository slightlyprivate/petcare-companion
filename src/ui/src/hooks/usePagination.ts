import { useState } from 'react';

interface UsePaginationReturn {
  currentPage: number;
  setCurrentPage: (page: number) => void;
  nextPage: () => void;
  resetPage: () => void;
}

/**
 * Hook to manage pagination state
 */
export function usePagination(initialPage = 1): UsePaginationReturn {
  const [currentPage, setCurrentPage] = useState(initialPage);

  const nextPage = () => {
    setCurrentPage((prev) => prev + 1);
  };

  const resetPage = () => {
    setCurrentPage(1);
  };

  return {
    currentPage,
    setCurrentPage,
    nextPage,
    resetPage,
  };
}
