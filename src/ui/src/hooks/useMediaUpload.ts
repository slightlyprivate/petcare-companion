import { useState, useEffect, type ChangeEvent } from 'react';
import { useUploadMedia } from '../api/uploads/hooks';

type UploadContext = 'activities' | 'pet_avatars' | 'general';

interface UseMediaUploadOptions {
  context?: UploadContext;
  onUploadSuccess?: (url: string) => void;
  onUploadError?: (error: string) => void;
}

/**
 * Hook for managing file uploads with preview support
 */
export function useMediaUpload(options: UseMediaUploadOptions = {}) {
  const { context = 'activities', onUploadSuccess, onUploadError } = options;

  const [mediaUrl, setMediaUrl] = useState('');
  const [mediaPreview, setMediaPreview] = useState<string | null>(null);
  const [uploadError, setUploadError] = useState('');

  const uploadMedia = useUploadMedia();

  const handleMediaUrlChange = (value: string) => {
    if (mediaPreview?.startsWith('blob:')) {
      URL.revokeObjectURL(mediaPreview);
    }
    setMediaPreview(null);
    setUploadError('');
    setMediaUrl(value);
  };

  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setUploadError('');
    try {
      const result = await uploadMedia.mutateAsync({ file, context });
      if (mediaPreview?.startsWith('blob:')) {
        URL.revokeObjectURL(mediaPreview);
      }
      setMediaPreview(URL.createObjectURL(file));
      setMediaUrl(result.url);
      onUploadSuccess?.(result.url);
    } catch (err) {
      const errorMessage = (err as Error).message || 'Failed to upload file';
      setUploadError(errorMessage);
      onUploadError?.(errorMessage);
    }
  };

  const clearMediaSelection = () => {
    if (mediaPreview?.startsWith('blob:')) {
      URL.revokeObjectURL(mediaPreview);
    }
    setMediaPreview(null);
    setMediaUrl('');
    setUploadError('');
  };

  const resetMedia = () => {
    clearMediaSelection();
  };

  // Cleanup blob URLs on unmount
  useEffect(() => {
    return () => {
      if (mediaPreview?.startsWith('blob:')) {
        URL.revokeObjectURL(mediaPreview);
      }
    };
  }, [mediaPreview]);

  return {
    mediaUrl,
    mediaPreview,
    uploadError,
    isUploading: uploadMedia.isPending,
    handleMediaUrlChange,
    handleFileChange,
    clearMediaSelection,
    resetMedia,
    setMediaUrl,
  };
}
