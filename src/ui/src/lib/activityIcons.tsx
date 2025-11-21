import type { IconName } from '../components/icons';

/**
 * Map activity types to icon names used by the shared Icon component
 */
const activityIconMap: Record<string, IconName> = {
  feeding: 'feeding',
  walk: 'walk',
  play: 'play',
  grooming: 'grooming',
  vet: 'vet',
  medication: 'medication',
  training: 'other',
};

export function getActivityIconName(type: string): IconName {
  return activityIconMap[type] ?? 'other';
}
