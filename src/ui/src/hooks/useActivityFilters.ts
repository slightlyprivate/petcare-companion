import { useState } from 'react';

interface ActivityFiltersState {
  selectedType: string;
  dateFrom: string;
  dateTo: string;
}

interface UseActivityFiltersReturn extends ActivityFiltersState {
  setSelectedType: (type: string) => void;
  setDateFrom: (date: string) => void;
  setDateTo: (date: string) => void;
  clearFilters: () => void;
  activeFilterCount: number;
  hasActiveFilters: boolean;
}

/**
 * Hook to manage activity filter state
 */
export function useActivityFilters(): UseActivityFiltersReturn {
  const [selectedType, setSelectedType] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  const clearFilters = () => {
    setSelectedType('');
    setDateFrom('');
    setDateTo('');
  };

  const activeFilterCount = [selectedType, dateFrom, dateTo].filter(Boolean).length;
  const hasActiveFilters = activeFilterCount > 0;

  return {
    selectedType,
    dateFrom,
    dateTo,
    setSelectedType,
    setDateFrom,
    setDateTo,
    clearFilters,
    activeFilterCount,
    hasActiveFilters,
  };
}
