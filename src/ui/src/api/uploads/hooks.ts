import { useAppMutation } from '../../lib/appQuery';
import { uploadMedia } from './client';

/**
 * Hook to upload media files
 */
export function useUploadMedia() {
  return useAppMutation({
    mutationFn: uploadMedia,
  });
}
