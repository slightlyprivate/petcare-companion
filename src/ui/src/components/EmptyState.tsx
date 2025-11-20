interface EmptyStateProps {
  icon?: React.ReactNode;
  title: string;
  description?: string;
  variant?: 'simple' | 'dashed';
}

/**
 * EmptyState Component
 *
 * A reusable component to display an empty state message with optional icon and description.
 *
 * @param {React.ReactNode} [icon] - An optional icon to display above the title.
 * @param {string} title - The main title text for the empty state.
 * @param {string} [description] - An optional description text for additional context.
 * @param {'simple' | 'dashed'} [variant='simple'] - The style variant of the empty state.
 * @returns {JSX.Element} The rendered empty state component.
 */
export default function EmptyState({
  icon,
  title,
  description,
  variant = 'simple',
}: EmptyStateProps): JSX.Element {
  if (variant === 'dashed') {
    return (
      <div className="rounded-lg border-2 border-dashed border-brand-muted bg-brand-smoke p-8 text-center">
        {icon && <div className="mx-auto h-12 w-12 text-brand-fg/30">{icon}</div>}
        <p className="mt-2 text-sm font-medium text-brand-fg">{title}</p>
        {description && <p className="mt-1 text-xs text-brand-fg/60">{description}</p>}
      </div>
    );
  }

  return (
    <div className="rounded-lg border border-brand-muted bg-brand-secondary/20 p-6 text-center">
      <p className="text-sm text-brand-fg/60">{title}</p>
      {description && <p className="mt-1 text-xs text-brand-fg/40">{description}</p>}
    </div>
  );
}
