interface EmptyStateProps {
  canCreate?: boolean;
}

/**
 * Empty state displayed when no activities exist
 */
export default function ActivityEmptyState({ canCreate = false }: EmptyStateProps) {
  return (
    <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
      <p className="text-sm text-brand-fg/60">No activities logged yet</p>
      {canCreate && (
        <p className="mt-1 text-xs text-brand-fg/40">Start tracking your pet's daily activities</p>
      )}
    </div>
  );
}
