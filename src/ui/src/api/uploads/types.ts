export interface UploadPayload {
  file: File;
  context?: 'activities' | 'pet_avatars' | 'general';
}

export interface UploadResponse {
  path: string;
  url: string;
  disk: string;
  context: string;
  original_name: string;
  mime_type: string;
}
