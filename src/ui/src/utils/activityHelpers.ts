import type { ListActivitiesParams } from '../api/activities/types';

/**
 * Build query parameters for activity list API
 */
export function buildActivityQueryParams(
  page: number,
  selectedType: string,
  dateFrom: string,
  dateTo: string,
  perPage = 10,
): ListActivitiesParams {
  return {
    page,
    per_page: perPage,
    ...(selectedType && { type: selectedType }),
    ...(dateFrom && { date_from: dateFrom }),
    ...(dateTo && { date_to: dateTo }),
  };
}

/**
 * Extract pagination metadata from activities response
 */
export function getActivityPaginationInfo(
  meta: { current_page: number; last_page: number; total: number } | undefined,
) {
  const hasMore = meta ? meta.current_page < meta.last_page : false;
  const totalCount = meta?.total || 0;

  return { hasMore, totalCount };
}
