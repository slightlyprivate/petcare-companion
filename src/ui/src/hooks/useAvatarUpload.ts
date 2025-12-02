import { useState } from 'react';
import { updatePetAvatar } from '../api/pets/client';

type UseAvatarUploadOptions = {
  petId: string;
  onSuccess?: (avatarUrl: string) => void;
  onError?: (error: Error) => void;
};

/**
 * Hook for uploading pet avatars
 */
export function useAvatarUpload({ petId, onSuccess, onError }: UseAvatarUploadOptions) {
  const [isUploading, setIsUploading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  async function upload(file: File) {
    setIsUploading(true);
    setError(null);

    try {
      const result = await updatePetAvatar(petId, file);
      onSuccess?.(result.avatar_url);
      return result;
    } catch (err) {
      const error = err instanceof Error ? err : new Error('Upload failed');
      setError(error);
      onError?.(error);
      throw error;
    } finally {
      setIsUploading(false);
    }
  }

  return {
    upload,
    isUploading,
    error,
  };
}
