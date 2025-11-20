interface DaySelectorProps {
  selected: number[];
  onChange: (days: number[]) => void;
  onToggle?: (day: number) => void;
  disabled?: boolean;
}

const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/**
 * DaySelector Component
 *
 * Multi-select checkbox group for days of the week
 * Days are represented as numbers: 0=Sunday, 6=Saturday
 */
export default function DaySelector({
  selected,
  onChange,
  onToggle,
  disabled = false,
}: DaySelectorProps) {
  const handleToggle = (day: number) => {
    if (disabled) return;

    if (onToggle) {
      onToggle(day);
    } else {
      // Fallback if onToggle not provided
      const newSelected = selected.includes(day)
        ? selected.filter((d) => d !== day)
        : [...selected, day].sort();
      onChange(newSelected);
    }
  };

  return (
    <div className="space-y-2">
      <label className="block text-sm font-medium text-brand-fg">Days of Week *</label>
      <div className="flex flex-wrap gap-2">
        {DAY_NAMES.map((dayName, index) => {
          const isSelected = selected.includes(index);
          return (
            <button
              key={index}
              type="button"
              onClick={() => handleToggle(index)}
              disabled={disabled}
              className={`
                px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                ${
                  isSelected
                    ? 'bg-brand-accent text-white'
                    : 'bg-brand-bg border border-brand-border text-brand-fg hover:bg-brand-secondary/50'
                }
                ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
              `}
              aria-pressed={isSelected}
              aria-label={`${isSelected ? 'Deselect' : 'Select'} ${dayName}`}
            >
              {dayName}
            </button>
          );
        })}
      </div>
      {selected.length === 0 && (
        <p className="text-xs text-red-600">Please select at least one day</p>
      )}
    </div>
  );
}
