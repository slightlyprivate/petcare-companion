import { useState } from 'react';

interface UseDeleteConfirmationReturn<T> {
  itemToDelete: T | null;
  isConfirmOpen: boolean;
  confirmDelete: (item: T) => void;
  cancelDelete: () => void;
  executeDelete: (deleteFn: (item: T) => Promise<void>) => Promise<void>;
}

/**
 * Hook to manage delete confirmation flow
 */
export function useDeleteConfirmation<T = string | number>(): UseDeleteConfirmationReturn<T> {
  const [itemToDelete, setItemToDelete] = useState<T | null>(null);

  const confirmDelete = (item: T) => {
    setItemToDelete(item);
  };

  const cancelDelete = () => {
    setItemToDelete(null);
  };

  const executeDelete = async (deleteFn: (item: T) => Promise<void>) => {
    if (!itemToDelete) return;

    try {
      await deleteFn(itemToDelete);
      setItemToDelete(null);
    } catch (err) {
      console.error('Failed to delete:', err);
      throw err;
    }
  };

  return {
    itemToDelete,
    isConfirmOpen: itemToDelete !== null,
    confirmDelete,
    cancelDelete,
    executeDelete,
  };
}
