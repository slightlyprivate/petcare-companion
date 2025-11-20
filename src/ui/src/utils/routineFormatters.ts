/**
 * Utility functions for formatting routine-related data
 */

export const TIME_OF_DAY_LABELS: Record<string, string> = {
  morning: '🌅 Morning',
  afternoon: '☀️ Afternoon',
  evening: '🌇 Evening',
  night: '🌙 Night',
};

export const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/**
 * Formats a time of day string into a human-readable label
 */
export function formatTimeOfDay(timeOfDay: string): string {
  return TIME_OF_DAY_LABELS[timeOfDay] || timeOfDay;
}

/**
 * Formats an array of day numbers into a human-readable string
 */
export function formatDaysOfWeek(days: number[]): string {
  if (days.length === 7) return 'Every day';
  if (days.length === 0) return 'No days';
  return days
    .sort()
    .map((d) => DAY_NAMES[d])
    .join(', ');
}
