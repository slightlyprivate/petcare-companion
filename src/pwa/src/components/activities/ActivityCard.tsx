import Button from '../Button';
import { cn } from '../../lib/cn';
import { resolveAssetUrl } from '../../lib/assets';
import { formatRelativeTime } from '../../lib/dateUtils';
import { getActivityTypeInfo } from '../../lib/activityTypes';
import { getActivityIconName } from '../../lib/activityIcons';
import { Icon } from '../icons';

interface Activity {
  id: string | number;
  type: string;
  description: string;
  media_url?: string | null;
  created_at: string;
}

interface ActivityCardProps {
  activity: Activity;
  canDelete?: boolean;
  imageLoadError?: boolean;
  onDelete: (id: string | number) => void;
  onImageError: (id: string | number) => void;
}

/**
 * Card displaying a single activity entry
 */
export default function ActivityCard({
  activity,
  canDelete = false,
  imageLoadError = false,
  onDelete,
  onImageError,
}: ActivityCardProps) {
  const typeInfo = getActivityTypeInfo(activity.type);
  const iconName = getActivityIconName(activity.type);
  const mediaLink = resolveAssetUrl(activity.media_url);

  return (
    <div className="relative rounded-lg border border-brand-muted bg-white p-4 shadow-sm">
      <div className="flex items-start justify-between gap-3">
        <div className="flex-1">
          <div className="flex items-center gap-2 mb-1">
            <div className="flex items-center gap-1.5">
              <Icon name={iconName} className="w-4 h-4" size={16} />
              <span className={cn('rounded-full px-2 py-0.5 text-xs font-medium', typeInfo.color)}>
                {typeInfo.label}
              </span>
            </div>
            <span className="text-xs text-brand-fg/40">
              {formatRelativeTime(activity.created_at)}
            </span>
          </div>
          <p className="text-sm text-brand-fg">{activity.description}</p>
          {activity.media_url && mediaLink && (
            <div className="mt-2 space-y-2">
              {!imageLoadError && (
                <div className="overflow-hidden rounded-lg border border-brand-muted bg-brand-secondary/20">
                  <img
                    src={mediaLink}
                    alt="Activity media"
                    className="max-h-48 w-full object-cover"
                    onError={() => onImageError(activity.id)}
                  />
                </div>
              )}
              <a
                href={mediaLink}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-block text-xs text-brand-accent hover:underline"
              >
                View Media →
              </a>
            </div>
          )}
        </div>
        {canDelete && (
          <Button
            variant="ghost"
            size="sm"
            onClick={() => onDelete(activity.id)}
            className="text-red-600 hover:bg-red-50"
          >
            Delete
          </Button>
        )}
      </div>
    </div>
  );
}
