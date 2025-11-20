import { usePetActivities, useDeletePetActivity } from '../../api/activities/hooks';
import { useActivityForm } from '../../hooks/useActivityForm';
import { useMediaUpload } from '../../hooks/useMediaUpload';
import { useImageLoadError } from '../../hooks/useImageLoadError';
import ActivityForm from './ActivityForm';
import ActivityCard from './ActivityCard';
import ActivityEmptyState from './ActivityEmptyState';
import Button from '../Button';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner';

interface ActivityTimelineProps {
  petId: string | number;
  canCreate?: boolean;
  canDelete?: boolean;
}

/**
 * Activity timeline component showing pet activities with ability to add new ones.
 */
export default function ActivityTimeline({
  petId,
  canCreate = false,
  canDelete = false,
}: ActivityTimelineProps) {
  const { data: activitiesData, isLoading, error } = usePetActivities(petId);
  const deleteActivity = useDeletePetActivity();

  const {
    showAddForm,
    activityType,
    description,
    formError,
    isSubmitting,
    setActivityType,
    setDescription,
    handleSubmit: submitActivity,
    toggleForm,
  } = useActivityForm({ petId });

  const {
    mediaUrl,
    mediaPreview,
    uploadError,
    isUploading,
    handleMediaUrlChange,
    handleFileChange,
    clearMediaSelection,
    resetMedia,
  } = useMediaUpload({ context: 'activities' });

  const { imageLoadErrors, handleImageError } = useImageLoadError();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const success = await submitActivity(mediaUrl);
    if (success) {
      resetMedia();
    }
  };

  const handleDelete = async (activityId: string | number) => {
    if (!window.confirm('Are you sure you want to delete this activity?')) {
      return;
    }

    try {
      await deleteActivity.mutateAsync(activityId);
    } catch (err) {
      console.error('Failed to delete activity:', err);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-8">
        <Spinner />
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load activities" />;
  }

  const activities = activitiesData?.data || [];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold text-brand-fg">Activity Timeline</h2>
        {canCreate && (
          <Button variant="secondary" size="sm" onClick={toggleForm} disabled={isSubmitting}>
            {showAddForm ? 'Cancel' : 'Log Activity'}
          </Button>
        )}
      </div>

      {showAddForm && (
        <ActivityForm
          activityType={activityType}
          description={description}
          mediaUrl={mediaUrl}
          mediaPreview={mediaPreview}
          formError={formError}
          uploadError={uploadError}
          isSubmitting={isSubmitting}
          isUploading={isUploading}
          onActivityTypeChange={setActivityType}
          onDescriptionChange={setDescription}
          onMediaUrlChange={handleMediaUrlChange}
          onFileChange={handleFileChange}
          onClearMedia={clearMediaSelection}
          onSubmit={handleSubmit}
        />
      )}

      <div className="space-y-3">
        {activities.length === 0 ? (
          <ActivityEmptyState canCreate={canCreate} />
        ) : (
          activities.map((activity) => (
            <ActivityCard
              key={activity.id}
              activity={activity}
              canDelete={canDelete}
              imageLoadError={imageLoadErrors[activity.id]}
              onDelete={handleDelete}
              onImageError={handleImageError}
            />
          ))
        )}
      </div>

      {activitiesData?.meta && activitiesData.meta.total > activities.length && (
        <div className="text-center">
          <p className="text-xs text-brand-fg/60">
            Showing {activities.length} of {activitiesData.meta.total} activities
          </p>
        </div>
      )}
    </div>
  );
}
