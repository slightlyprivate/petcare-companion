import type { ChangeEvent } from 'react';
import Button from '../Button';
import { cn } from '../../lib/cn';
import { ACTIVITY_TYPES } from '../../lib/activityTypes';

interface ActivityFormProps {
  activityType: string;
  description: string;
  mediaUrl: string;
  mediaPreview: string | null;
  formError: string;
  uploadError: string;
  isSubmitting: boolean;
  isUploading: boolean;
  onActivityTypeChange: (value: string) => void;
  onDescriptionChange: (value: string) => void;
  onMediaUrlChange: (value: string) => void;
  onFileChange: (e: ChangeEvent<HTMLInputElement>) => void;
  onClearMedia: () => void;
  onSubmit: (e: React.FormEvent) => void;
}

/**
 * Form for creating a new activity entry
 */
export default function ActivityForm({
  activityType,
  description,
  mediaUrl,
  mediaPreview,
  formError,
  uploadError,
  isSubmitting,
  isUploading,
  onActivityTypeChange,
  onDescriptionChange,
  onMediaUrlChange,
  onFileChange,
  onClearMedia,
  onSubmit,
}: ActivityFormProps) {
  return (
    <form
      onSubmit={onSubmit}
      className="rounded-lg border border-brand-muted bg-brand-secondary/30 p-4 space-y-3"
    >
      <div>
        <label htmlFor="activity-type" className="mb-1 block text-sm font-medium text-brand-fg">
          Activity Type
        </label>
        <select
          id="activity-type"
          value={activityType}
          onChange={(e) => onActivityTypeChange(e.target.value)}
          className="w-full rounded border border-brand-muted px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent"
          disabled={isSubmitting}
        >
          {ACTIVITY_TYPES.map((type) => (
            <option key={type.value} value={type.value}>
              {type.label}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label
          htmlFor="activity-description"
          className="mb-1 block text-sm font-medium text-brand-fg"
        >
          Description
        </label>
        <textarea
          id="activity-description"
          value={description}
          onChange={(e) => onDescriptionChange(e.target.value)}
          placeholder="What happened?"
          rows={3}
          className={cn(
            'w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-2',
            formError
              ? 'border-red-500 focus:ring-red-500'
              : 'border-brand-muted focus:ring-brand-accent',
          )}
          disabled={isSubmitting}
          required
        />
      </div>

      <div className="space-y-2">
        <label
          htmlFor="activity-media-file"
          className="mb-1 block text-sm font-medium text-brand-fg"
        >
          Upload media (images or MP4/WebM)
        </label>
        <input
          id="activity-media-file"
          type="file"
          accept="image/*,video/mp4,video/webm"
          onChange={onFileChange}
          disabled={isSubmitting || isUploading}
          className="block w-full text-sm text-brand-fg file:mr-4 file:cursor-pointer file:rounded file:border-0 file:bg-brand-accent file:px-3 file:py-2 file:text-white"
        />
        <p className="text-xs text-brand-fg/60">
          Files are stored in shared storage. You can also paste an external URL or storage path
          below.
        </p>
      </div>

      <div>
        <label htmlFor="activity-media" className="mb-1 block text-sm font-medium text-brand-fg">
          Media link or storage path (optional)
        </label>
        <input
          id="activity-media"
          type="text"
          value={mediaUrl}
          onChange={(e) => onMediaUrlChange(e.target.value)}
          placeholder="https://example.com/image.jpg or activities/media/photo.jpg"
          className="w-full rounded border border-brand-muted px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent"
          disabled={isSubmitting || isUploading}
        />
      </div>

      {(mediaPreview || mediaUrl) && (
        <div className="flex items-center justify-between rounded border border-brand-muted bg-white px-3 py-2">
          <div className="flex items-center gap-3">
            {mediaPreview ? (
              <img
                src={mediaPreview}
                alt="Selected media preview"
                className="h-12 w-12 rounded object-cover"
              />
            ) : (
              <div className="flex h-12 w-12 items-center justify-center rounded bg-brand-secondary/50 text-sm text-brand-fg/60">
                Link
              </div>
            )}
            <p className="text-xs text-brand-fg/70 break-all">
              {mediaPreview ? 'Uploaded file ready to attach' : mediaUrl}
            </p>
          </div>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={onClearMedia}
            disabled={isSubmitting}
          >
            Clear
          </Button>
        </div>
      )}

      {formError && <p className="text-sm text-red-600">{formError}</p>}
      {uploadError && <p className="text-sm text-red-600">{uploadError}</p>}

      <Button
        type="submit"
        size="sm"
        isLoading={isSubmitting}
        className="w-full"
        disabled={isUploading}
      >
        Log Activity
      </Button>
    </form>
  );
}
