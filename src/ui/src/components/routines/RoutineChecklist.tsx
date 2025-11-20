import { useTodayTasks, useCompleteRoutineOccurrence } from '../../api/routines/hooks';
import ErrorMessage from '../ErrorMessage';
import Spinner from '../Spinner';
import { cn } from '../../lib/cn';

interface RoutineChecklistProps {
  petId: string | number;
  canComplete?: boolean;
}

const TIME_OF_DAY_LABELS: Record<string, string> = {
  morning: '🌅 Morning',
  afternoon: '☀️ Afternoon',
  evening: '🌇 Evening',
  night: '🌙 Night',
};

const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/**
 * Daily routine checklist component showing today's tasks with completion tracking.
 */
export default function RoutineChecklist({ petId, canComplete = false }: RoutineChecklistProps) {
  const { data: tasksData, isLoading, error } = useTodayTasks(petId);
  const completeTask = useCompleteRoutineOccurrence();

  const handleComplete = async (occurrenceId: string | number) => {
    try {
      await completeTask.mutateAsync(occurrenceId);
    } catch (err) {
      console.error('Failed to complete task:', err);
    }
  };

  const formatTimeOfDay = (timeOfDay: string) => {
    return TIME_OF_DAY_LABELS[timeOfDay] || timeOfDay;
  };

  const formatDaysOfWeek = (days: number[]) => {
    if (days.length === 7) return 'Every day';
    if (days.length === 0) return 'No days';
    return days
      .sort()
      .map((d) => DAY_NAMES[d])
      .join(', ');
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-8">
        <Spinner />
      </div>
    );
  }

  if (error) {
    return <ErrorMessage message="Failed to load today's tasks" />;
  }

  const tasks = tasksData?.data || [];
  const completedCount = tasks.filter((t) => t.completed_at).length;
  const totalCount = tasks.length;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold text-brand-fg">Today's Routines</h2>
          {totalCount > 0 && (
            <p className="text-sm text-brand-fg/60">
              {completedCount} of {totalCount} completed
            </p>
          )}
        </div>
        {totalCount > 0 && (
          <div className="flex items-center gap-2">
            <div className="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
              <div
                className="h-full bg-brand-accent transition-all duration-300"
                style={{ width: `${(completedCount / totalCount) * 100}%` }}
              />
            </div>
            <span className="text-sm font-medium text-brand-fg">
              {Math.round((completedCount / totalCount) * 100)}%
            </span>
          </div>
        )}
      </div>

      <div className="space-y-2">
        {tasks.length === 0 ? (
          <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
            <p className="text-sm text-brand-fg/60">No routines scheduled for today</p>
            <p className="mt-1 text-xs text-brand-fg/40">
              Create routines to track daily tasks for your pet
            </p>
          </div>
        ) : (
          tasks.map((task) => {
            const isCompleted = !!task.completed_at;
            const routine = task.routine;

            return (
              <div
                key={task.id}
                className={cn(
                  'rounded-lg border bg-white p-4 shadow-sm transition-all',
                  isCompleted
                    ? 'border-green-200 bg-green-50/50 opacity-75'
                    : 'border-brand-muted hover:border-brand-accent',
                )}
              >
                <div className="flex items-start gap-3">
                  {canComplete && !isCompleted ? (
                    <button
                      onClick={() => handleComplete(task.id)}
                      disabled={completeTask.isPending}
                      className={cn(
                        'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-all',
                        'border-brand-accent hover:bg-brand-accent hover:text-white',
                        'disabled:opacity-50 disabled:cursor-not-allowed',
                      )}
                      aria-label="Mark as complete"
                    >
                      {completeTask.isPending ? (
                        <div className="h-3 w-3 animate-spin rounded-full border-2 border-brand-accent border-t-transparent" />
                      ) : null}
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
                      {isCompleted && (
                        <svg
                          className="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={3}
                            d="M5 13l4 4L19 7"
                          />
                        </svg>
                      )}
                    </div>
                  )}

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
                      <div className="flex flex-col items-end gap-1">
                        <span className="whitespace-nowrap rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                          {formatTimeOfDay(routine.time_of_day)}
                        </span>
                      </div>
                    </div>

                    <div className="mt-2 flex items-center gap-3 text-xs text-brand-fg/40">
                      <span>📅 {formatDaysOfWeek(routine.days_of_week)}</span>
                      {isCompleted && task.completed_at && (
                        <span>
                          ✓ Completed at {new Date(task.completed_at).toLocaleTimeString()}
                        </span>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {completedCount === totalCount && totalCount > 0 && (
        <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-center">
          <p className="text-sm font-medium text-green-800">🎉 All tasks completed for today!</p>
        </div>
      )}
    </div>
  );
}
