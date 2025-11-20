import { api, unwrapResource } from '../../lib/fetch';
import type { UploadPayload, UploadResponse } from './types';

/**
 * Upload media file to the server
 */
export const uploadMedia = async (payload: UploadPayload): Promise<UploadResponse> => {
  const formData = new FormData();
  formData.append('file', payload.file);
  if (payload.context) {
    formData.append('context', payload.context);
  }

  const res = await api<{ data: UploadResponse }>('/uploads', {
    method: 'POST',
    body: formData,
    json: false,
  });

  return unwrapResource<UploadResponse>(res) ?? (res as unknown as UploadResponse);
};
