import { useState } from 'react';

/**
 * Hook for tracking image load errors by ID
 */
export function useImageLoadError() {
  const [imageLoadErrors, setImageLoadErrors] = useState<Record<string | number, boolean>>({});

  const handleImageError = (id: string | number) => {
    setImageLoadErrors((prev) => ({
      ...prev,
      [id]: true,
    }));
  };

  const hasError = (id: string | number) => imageLoadErrors[id] || false;

  const clearError = (id: string | number) => {
    setImageLoadErrors((prev) => {
      const newErrors = { ...prev };
      delete newErrors[id];
      return newErrors;
    });
  };

  const clearAllErrors = () => {
    setImageLoadErrors({});
  };

  return {
    imageLoadErrors,
    handleImageError,
    hasError,
    clearError,
    clearAllErrors,
  };
}
