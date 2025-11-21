import { useState, useRef, ChangeEvent } from 'react';
import Button from './Button';
import { resolveAssetUrl } from '../lib/assets';

type PetAvatarUploaderProps = {
  petId: string;
  currentAvatarUrl?: string | null;
  onUploadSuccess: (avatarUrl: string) => void;
  onUploadFile: (file: File) => Promise<{ avatar_url: string }>;
};

export default function PetAvatarUploader({
  petId,
  currentAvatarUrl,
  onUploadSuccess,
  onUploadFile,
}: PetAvatarUploaderProps) {
  const [preview, setPreview] = useState<string | null>(null);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const displayUrl = preview || (currentAvatarUrl ? resolveAssetUrl(currentAvatarUrl) : null);

  function handleFileChange(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      setError('Please select an image file');
      return;
    }

    setSelectedFile(file);
    setError(null);

    const reader = new FileReader();
    reader.onload = (e) => {
      setPreview(e.target?.result as string);
    };
    reader.readAsDataURL(file);
  }

  async function handleUpload() {
    if (!selectedFile) return;

    setUploading(true);
    setError(null);

    try {
      const result = await onUploadFile(selectedFile);
      setPreview(null);
      setSelectedFile(null);
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
      onUploadSuccess(result.avatar_url);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Upload failed');
    } finally {
      setUploading(false);
    }
  }

  function handleClear() {
    setPreview(null);
    setSelectedFile(null);
    setError(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  }

  return (
    <div className="space-y-3" data-pet-id={petId}>
      <div className="text-sm font-medium">Pet Avatar</div>

      {displayUrl && (
        <div className="flex items-start gap-3">
          <img
            src={displayUrl}
            alt="Pet avatar"
            className="h-24 w-24 object-cover rounded-full border"
          />
          {preview && (
            <div className="text-xs text-gray-600 self-center">
              Preview of new avatar (not yet saved)
            </div>
          )}
        </div>
      )}

      <div>
        <input
          ref={fileInputRef}
          type="file"
          accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
          onChange={handleFileChange}
          className="text-sm"
        />
      </div>

      {error && <div className="text-sm text-red-600">{error}</div>}

      {selectedFile && (
        <div className="flex gap-2">
          <Button onClick={handleUpload} isLoading={uploading} type="button">
            Upload Avatar
          </Button>
          <Button onClick={handleClear} type="button" disabled={uploading}>
            Cancel
          </Button>
        </div>
      )}
    </div>
  );
}
