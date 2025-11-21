import type { PetRoutine, PetRoutineOccurrence } from '../../api/routines/types';
import { formatTimeOfDay, formatDaysOfWeek } from '../../utils/routineFormatters';
import { cn } from '../../lib/cn';
import { Icon } from '../icons';

interface RoutineTaskCardProps {
  task: PetRoutineOccurrence;
  canComplete?: boolean;
  canManage?: boolean;
  isCompleting?: boolean;
  onComplete: (occurrenceId: string | number) => void;
  onEdit: (routine: PetRoutine) => void;
  onDelete: (routineId: string | number) => void;
}

/**
 * Individual routine task card with completion tracking and management actions
 */
export default function RoutineTaskCard({
  task,
  canComplete = false,
  canManage = false,
  isCompleting = false,
  onComplete,
  onEdit,
  onDelete,
}: RoutineTaskCardProps) {
  const isCompleted = !!task.completed_at;
  const routine = task.routine;

  return (
    <div
      className={cn(
        'rounded-lg border bg-white p-4 shadow-sm transition-all',
        isCompleted
          ? 'border-green-200 bg-green-50/50 opacity-75'
          : 'border-brand-muted hover:border-brand-accent',
      )}
    >
      <div className="flex items-start gap-3">
        {/* Completion Checkbox */}
        {canComplete && !isCompleted ? (
          <button
            onClick={() => onComplete(task.id)}
            disabled={isCompleting}
            className={cn(
              'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-all',
              'border-brand-accent hover:bg-brand-accent hover:text-white',
              'disabled:opacity-50 disabled:cursor-not-allowed',
            )}
            aria-label="Mark as complete"
          >
            {isCompleting && <Icon name="spinner" size={12} className="animate-spin" />}
          </button>
        ) : (
          <div
            className={cn(
              'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2',
              isCompleted
                ? 'border-green-500 bg-green-500 text-white'
                : 'border-gray-300 bg-gray-100',
            )}
          >
            {isCompleted && <Icon name="check" size={16} strokeWidth={3} />}
          </div>
        )}

        {/* Task Content */}
        <div className="flex-1 min-w-0">
          <div className="flex items-start justify-between gap-2">
            <div className="flex-1">
              <h3
                className={cn(
                  'font-medium',
                  isCompleted ? 'text-brand-fg/60 line-through' : 'text-brand-fg',
                )}
              >
                {routine.name}
              </h3>
              {routine.description && (
                <p className="mt-1 text-sm text-brand-fg/60">{routine.description}</p>
              )}
            </div>

            {/* Time Badge and Actions */}
            <div className="flex items-center gap-2">
              <span className="whitespace-nowrap rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                {formatTimeOfDay(routine.time_of_day)}
              </span>
              {canManage && (
                <div className="flex gap-1">
                  <button
                    onClick={() => onEdit(routine)}
                    className="rounded p-1 text-brand-fg/60 hover:bg-brand-secondary/50 hover:text-brand-fg"
                    title="Edit routine"
                  >
                    <Icon name="pencil" size={16} />
                  </button>
                  <button
                    onClick={() => onDelete(routine.id)}
                    className="rounded p-1 text-red-600/60 hover:bg-red-50 hover:text-red-600"
                    title="Delete routine"
                  >
                    <Icon name="trash" size={16} />
                  </button>
                </div>
              )}
            </div>
          </div>

          {/* Footer: Days and Completion Time */}
          <div className="mt-2 flex items-center gap-3 text-xs text-brand-fg/40">
            <span>📅 {formatDaysOfWeek(routine.days_of_week)}</span>
            {isCompleted && task.completed_at && (
              <span>✓ Completed at {new Date(task.completed_at).toLocaleTimeString()}</span>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
