import { useState } from 'react';
import {
  usePetActivities,
  useCreatePetActivity,
  useDeletePetActivity,
} from '../../api/activities/hooks';
import Button from '../Button';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner';
import { cn } from '../../lib/cn';

interface ActivityTimelineProps {
  petId: string | number;
  canCreate?: boolean;
  canDelete?: boolean;
}

const ACTIVITY_TYPES = [
  { value: 'feeding', label: '🍽️ Feeding', color: 'bg-orange-100 text-orange-700' },
  { value: 'walk', label: '🚶 Walk', color: 'bg-green-100 text-green-700' },
  { value: 'play', label: '🎾 Play', color: 'bg-purple-100 text-purple-700' },
  { value: 'grooming', label: '✂️ Grooming', color: 'bg-blue-100 text-blue-700' },
  { value: 'vet', label: '🏥 Vet Visit', color: 'bg-red-100 text-red-700' },
  { value: 'medication', label: '💊 Medication', color: 'bg-pink-100 text-pink-700' },
  { value: 'training', label: '🎓 Training', color: 'bg-indigo-100 text-indigo-700' },
  { value: 'other', label: '📝 Other', color: 'bg-gray-100 text-gray-700' },
];

/**
 * Activity timeline component showing pet activities with ability to add new ones.
 */
export default function ActivityTimeline({
  petId,
  canCreate = false,
  canDelete = false,
}: ActivityTimelineProps) {
  const [showAddForm, setShowAddForm] = useState(false);
  const [activityType, setActivityType] = useState('feeding');
  const [description, setDescription] = useState('');
  const [mediaUrl, setMediaUrl] = useState('');
  const [formError, setFormError] = useState('');

  const { data: activitiesData, isLoading, error } = usePetActivities(petId);
  const createActivity = useCreatePetActivity();
  const deleteActivity = useDeletePetActivity();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError('');

    if (!description.trim()) {
      setFormError('Description is required');
      return;
    }

    try {
      await createActivity.mutateAsync({
        petId,
        type: activityType,
        description: description.trim(),
        media_url: mediaUrl.trim() || null,
      });
      setDescription('');
      setMediaUrl('');
      setActivityType('feeding');
      setShowAddForm(false);
    } catch (err) {
      setFormError((err as Error).message || 'Failed to create activity');
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

  const getActivityTypeInfo = (type: string) => {
    return (
      ACTIVITY_TYPES.find((t) => t.value === type) || ACTIVITY_TYPES[ACTIVITY_TYPES.length - 1]
    );
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return date.toLocaleDateString();
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
          <Button
            variant="secondary"
            size="sm"
            onClick={() => setShowAddForm(!showAddForm)}
            disabled={createActivity.isPending}
          >
            {showAddForm ? 'Cancel' : 'Log Activity'}
          </Button>
        )}
      </div>

      {showAddForm && (
        <form
          onSubmit={handleSubmit}
          className="rounded-lg border border-brand-muted bg-brand-secondary/30 p-4 space-y-3"
        >
          <div>
            <label htmlFor="activity-type" className="mb-1 block text-sm font-medium text-brand-fg">
              Activity Type
            </label>
            <select
              id="activity-type"
              value={activityType}
              onChange={(e) => setActivityType(e.target.value)}
              className="w-full rounded border border-brand-muted px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent"
              disabled={createActivity.isPending}
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
              onChange={(e) => setDescription(e.target.value)}
              placeholder="What happened?"
              rows={3}
              className={cn(
                'w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                formError
                  ? 'border-red-500 focus:ring-red-500'
                  : 'border-brand-muted focus:ring-brand-accent',
              )}
              disabled={createActivity.isPending}
              required
            />
          </div>

          <div>
            <label
              htmlFor="activity-media"
              className="mb-1 block text-sm font-medium text-brand-fg"
            >
              Media URL (optional)
            </label>
            <input
              id="activity-media"
              type="url"
              value={mediaUrl}
              onChange={(e) => setMediaUrl(e.target.value)}
              placeholder="https://example.com/image.jpg"
              className="w-full rounded border border-brand-muted px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent"
              disabled={createActivity.isPending}
            />
          </div>

          {formError && <p className="text-sm text-red-600">{formError}</p>}

          <Button type="submit" size="sm" isLoading={createActivity.isPending} className="w-full">
            Log Activity
          </Button>
        </form>
      )}

      <div className="space-y-3">
        {activities.length === 0 ? (
          <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
            <p className="text-sm text-brand-fg/60">No activities logged yet</p>
            {canCreate && (
              <p className="mt-1 text-xs text-brand-fg/40">
                Start tracking your pet's daily activities
              </p>
            )}
          </div>
        ) : (
          activities.map((activity) => {
            const typeInfo = getActivityTypeInfo(activity.type);
            return (
              <div
                key={activity.id}
                className="relative rounded-lg border border-brand-muted bg-white p-4 shadow-sm"
              >
                <div className="flex items-start justify-between gap-3">
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                      <span
                        className={cn(
                          'rounded-full px-2 py-0.5 text-xs font-medium',
                          typeInfo.color,
                        )}
                      >
                        {typeInfo.label}
                      </span>
                      <span className="text-xs text-brand-fg/40">
                        {formatDate(activity.created_at)}
                      </span>
                    </div>
                    <p className="text-sm text-brand-fg">{activity.description}</p>
                    {activity.media_url && (
                      <a
                        href={activity.media_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-2 inline-block text-xs text-brand-accent hover:underline"
                      >
                        View Media →
                      </a>
                    )}
                  </div>
                  {canDelete && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDelete(activity.id)}
                      disabled={deleteActivity.isPending}
                      className="text-red-600 hover:bg-red-50"
                    >
                      Delete
                    </Button>
                  )}
                </div>
              </div>
            );
          })
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
