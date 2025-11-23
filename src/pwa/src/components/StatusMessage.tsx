export default function StatusMessage({
  message,
  tone = 'info',
}: {
  message: string;
  tone?: 'success' | 'info' | 'warning';
}) {
  const toneCls =
    tone === 'success'
      ? 'bg-brand-muted/30 text-brand-primary border-brand-muted'
      : tone === 'warning'
        ? 'bg-brand-secondary text-brand-primary border-brand-secondary-200'
        : 'bg-brand-bg text-brand-fg border-brand-muted';
  return <div className={`text-sm px-3 py-2 rounded border ${toneCls}`}>{message}</div>;
}
