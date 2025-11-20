import { ACTIVITY_TYPES } from '../../lib/activityTypes';

interface ActivityFilterBarProps {
  selectedType: string;
  dateFrom: string;
  dateTo: string;
  onTypeChange: (type: string) => void;
  onDateFromChange: (date: string) => void;
  onDateToChange: (date: string) => void;
  onClearFilters: () => void;
  activeFilterCount: number;
}

/**
 * Filter bar for activity timeline with type and date range filters
 */
export default function ActivityFilterBar({
  selectedType,
  dateFrom,
  dateTo,
  onTypeChange,
  onDateFromChange,
  onDateToChange,
  onClearFilters,
  activeFilterCount,
}: ActivityFilterBarProps) {
  return (
    <div className="rounded-lg border border-brand-border bg-white p-4 space-y-3">
      <div className="flex flex-wrap items-end gap-3">
        {/* Activity Type Filter */}
        <div className="flex-1 min-w-[200px]">
          <label
            htmlFor="activity-type-filter"
            className="block text-sm font-medium text-brand-fg mb-1"
          >
            Activity Type
          </label>
          <select
            id="activity-type-filter"
            value={selectedType}
            onChange={(e) => onTypeChange(e.target.value)}
            className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary"
          >
            <option value="">All Types</option>
            {ACTIVITY_TYPES.map((type) => (
              <option key={type.value} value={type.value}>
                {type.label}
              </option>
            ))}
          </select>
        </div>

        {/* Date From Filter */}
        <div className="flex-1 min-w-40">
          <label
            htmlFor="date-from-filter"
            className="block text-sm font-medium text-brand-fg mb-1"
          >
            From Date
          </label>
          <input
            type="date"
            id="date-from-filter"
            value={dateFrom}
            onChange={(e) => onDateFromChange(e.target.value)}
            className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary"
          />
        </div>

        {/* Date To Filter */}
        <div className="flex-1 min-w-40">
          <label htmlFor="date-to-filter" className="block text-sm font-medium text-brand-fg mb-1">
            To Date
          </label>
          <input
            type="date"
            id="date-to-filter"
            value={dateTo}
            onChange={(e) => onDateToChange(e.target.value)}
            className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary"
          />
        </div>

        {/* Clear Filters Button */}
        {activeFilterCount > 0 && (
          <button
            type="button"
            onClick={onClearFilters}
            className="px-4 py-2 text-sm font-medium text-brand-fg/70 hover:text-brand-fg border border-brand-border rounded-md hover:bg-brand-bg/50 transition-colors"
          >
            Clear {activeFilterCount} {activeFilterCount === 1 ? 'filter' : 'filters'}
          </button>
        )}
      </div>
    </div>
  );
}
