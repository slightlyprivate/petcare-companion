/**
 * Activity type definitions and utilities
 */

export interface ActivityType {
  value: string;
  label: string;
  color: string;
}

export const ACTIVITY_TYPES: ActivityType[] = [
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
 * Get activity type info by value
 */
export function getActivityTypeInfo(type: string): ActivityType {
  return ACTIVITY_TYPES.find((t) => t.value === type) || ACTIVITY_TYPES[ACTIVITY_TYPES.length - 1];
}
