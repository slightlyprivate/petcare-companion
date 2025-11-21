import DaySelector from './DaySelector';
import Button from '../Button';
import ErrorMessage from '../ErrorMessage';

interface RoutineFormProps {
  name: string;
  description: string;
  timeOfDay: string;
  daysOfWeek: number[];
  formError: string | null;
  isSubmitting: boolean;
  onNameChange: (name: string) => void;
  onDescriptionChange: (description: string) => void;
  onTimeOfDayChange: (time: string) => void;
  onDaysOfWeekChange: (days: number[]) => void;
  onToggleDayOfWeek: (day: number) => void;
  onSubmit: (e: React.FormEvent) => void;
  onCancel?: () => void;
  submitLabel?: string;
}

/**
 * RoutineForm Component
 *
 * Form for creating or editing a pet routine
 */
export default function RoutineForm({
  name,
  description,
  timeOfDay,
  daysOfWeek,
  formError,
  isSubmitting,
  onNameChange,
  onDescriptionChange,
  onTimeOfDayChange,
  onDaysOfWeekChange,
  onToggleDayOfWeek,
  onSubmit,
  onCancel,
  submitLabel = 'Create Routine',
}: RoutineFormProps) {
  return (
    <form onSubmit={onSubmit} className="space-y-4">
      {formError && <ErrorMessage message={formError} />}

      <div>
        <label htmlFor="routine-name" className="block text-sm font-medium text-brand-fg mb-1">
          Routine Name *
        </label>
        <input
          id="routine-name"
          type="text"
          value={name}
          onChange={(e) => onNameChange(e.target.value)}
          placeholder="e.g., Morning Feeding"
          maxLength={100}
          disabled={isSubmitting}
          className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary disabled:opacity-50"
          required
          autoFocus
        />
      </div>

      <div>
        <label
          htmlFor="routine-description"
          className="block text-sm font-medium text-brand-fg mb-1"
        >
          Description (Optional)
        </label>
        <textarea
          id="routine-description"
          value={description}
          onChange={(e) => onDescriptionChange(e.target.value)}
          placeholder="Add instructions or notes..."
          maxLength={1000}
          rows={3}
          disabled={isSubmitting}
          className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary disabled:opacity-50 resize-none"
        />
      </div>

      <div>
        <label htmlFor="routine-time" className="block text-sm font-medium text-brand-fg mb-1">
          Time of Day *
        </label>
        <input
          id="routine-time"
          type="time"
          value={timeOfDay}
          onChange={(e) => onTimeOfDayChange(e.target.value)}
          disabled={isSubmitting}
          className="w-full rounded-md border border-brand-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-primary disabled:opacity-50"
          required
        />
      </div>

      <DaySelector
        selected={daysOfWeek}
        onChange={onDaysOfWeekChange}
        onToggle={onToggleDayOfWeek}
        disabled={isSubmitting}
      />

      <div className="flex gap-2 justify-end pt-2">
        {onCancel && (
          <Button type="button" variant="ghost" onClick={onCancel} disabled={isSubmitting}>
            Cancel
          </Button>
        )}
        <Button type="submit" variant="primary" disabled={isSubmitting}>
          {isSubmitting ? 'Saving...' : submitLabel}
        </Button>
      </div>
    </form>
  );
}
