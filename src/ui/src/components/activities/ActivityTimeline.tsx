import { usePetActivities, useDeletePetActivity } from '../../api/activities/hooks';
import { useActivityForm } from '../../hooks/useActivityForm';
import { useMediaUpload } from '../../hooks/useMediaUpload';
import { useImageLoadError } from '../../hooks/useImageLoadError';
import { useActivityFilters } from '../../hooks/useActivityFilters';
import { usePagination } from '../../hooks/usePagination';
import { useDeleteConfirmation } from '../../hooks/useDeleteConfirmation';
import { useToast } from '../../lib/notifications';
import { buildActivityQueryParams, getActivityPaginationInfo } from '../../utils/activityHelpers';
import ActivityForm from './ActivityForm';
import ActivityCard from './ActivityCard';
import ActivityEmptyState from './ActivityEmptyState';
import ActivityFilterBar from './ActivityFilterBar';
import LoadMoreButton from './LoadMoreButton';
import Button from '../Button';
import ConfirmDialog from '../modals/ConfirmDialog';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner'; // keep legacy spinner fallback
import { Skeleton } from '../ui/Loader';

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
  // Custom hooks for state management
  const filters = useActivityFilters();
  const pagination = usePagination();
  const deleteConfirm = useDeleteConfirmation<string | number>();

  // Build query params
  const queryParams = buildActivityQueryParams(
    pagination.currentPage,
    filters.selectedType,
    filters.dateFrom,
    filters.dateTo,
  );

  const {
    data: activitiesData,
    isLoading,
    error,
    isFetching,
  } = usePetActivities(petId, queryParams);
  const deleteActivity = useDeletePetActivity();
  const toast = useToast();

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
    deleteConfirm.confirmDelete(activityId);
  };

  const confirmDelete = async () => {
    try {
      await deleteConfirm.executeDelete((id) => deleteActivity.mutateAsync(id));
      toast.success('Activity deleted');
    } catch (err) {
      toast.error((err as Error).message || 'Failed to delete activity');
    }
  };

  const handleClearFilters = () => {
    filters.clearFilters();
    pagination.resetPage();
  };

  const handleTypeChange = (type: string) => {
    filters.setSelectedType(type);
    pagination.resetPage();
  };

  const handleDateFromChange = (date: string) => {
    filters.setDateFrom(date);
    pagination.resetPage();
  };

  const handleDateToChange = (date: string) => {
    filters.setDateTo(date);
    pagination.resetPage();
  };

  // Derived state
  const activities = activitiesData?.data || [];
  const { hasMore, totalCount } = getActivityPaginationInfo(activitiesData?.meta);

  if (isLoading) {
    return (
      <div className="space-y-4" aria-label="Activities loading">
        <div className="flex items-center justify-between">
          <Skeleton className="h-6 w-48" />
          <Skeleton className="h-8 w-24" />
        </div>
        <div className="space-y-2">
          <Skeleton className="h-20 w-full" />
          <Skeleton className="h-20 w-full" />
          <Skeleton className="h-20 w-full" />
        </div>
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load activities" />;
  }

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

      <ActivityFilterBar
        selectedType={filters.selectedType}
        dateFrom={filters.dateFrom}
        dateTo={filters.dateTo}
        onTypeChange={handleTypeChange}
        onDateFromChange={handleDateFromChange}
        onDateToChange={handleDateToChange}
        onClearFilters={handleClearFilters}
        activeFilterCount={filters.activeFilterCount}
      />

      <div className="space-y-3">
        {activities.length === 0 ? (
          filters.hasActiveFilters ? (
            <div className="text-center py-8">
              <p className="text-sm text-brand-fg/60 mb-2">No activities match your filters</p>
              <button
                onClick={handleClearFilters}
                className="text-sm text-brand-accent hover:underline"
              >
                Clear filters
              </button>
            </div>
          ) : (
            <ActivityEmptyState canCreate={canCreate} />
          )
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

      {activities.length > 0 && (
        <LoadMoreButton
          isLoading={isFetching && pagination.currentPage > 1}
          hasMore={hasMore}
          onLoadMore={pagination.nextPage}
          currentCount={activities.length}
          totalCount={totalCount}
        />
      )}

      <ConfirmDialog
        isOpen={deleteConfirm.isConfirmOpen}
        title="Delete Activity"
        message="Are you sure you want to delete this activity? This action cannot be undone."
        confirmText="Delete"
        onConfirm={confirmDelete}
        onClose={deleteConfirm.cancelDelete}
        variant="danger"
        isLoading={deleteActivity.isPending}
      />
    </div>
  );
}
