interface RoutineEmptyStateProps {
  canCreate?: boolean;
  onCreateClick?: () => void;
}

/**
 * RoutineEmptyState Component
 *
 * Empty state displayed when a pet has no routines
 */
export default function RoutineEmptyState({
  canCreate = false,
  onCreateClick,
}: RoutineEmptyStateProps) {
  return (
    <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-8 text-center">
      <p className="text-sm text-brand-fg/60 mb-2">No routines created yet</p>
      {canCreate ? (
        <>
          <p className="text-xs text-brand-fg/40 mb-4">
            Create daily routines to help track your pet&apos;s schedule
          </p>
          {onCreateClick && (
            <button
              onClick={onCreateClick}
              className="text-sm text-brand-accent hover:underline font-medium"
            >
              Create your first routine
            </button>
          )}
        </>
      ) : (
        <p className="text-xs text-brand-fg/40">
          The owner can create routines to track daily tasks
        </p>
      )}
    </div>
  );
}
