interface RoutineProgressProps {
  completed: number;
  total: number;
}

/**
 * Progress bar showing routine completion percentage
 */
export default function RoutineProgress({ completed, total }: RoutineProgressProps) {
  if (total === 0) return null;

  const percentage = Math.round((completed / total) * 100);

  return (
    <div className="flex items-center gap-2">
      <div className="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
        <div
          className="h-full bg-brand-accent transition-all duration-300"
          style={{ width: `${percentage}%` }}
        />
      </div>
      <span className="text-sm font-medium text-brand-fg">{percentage}%</span>
    </div>
  );
}
