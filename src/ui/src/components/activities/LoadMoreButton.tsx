import Button from '../Button';

interface LoadMoreButtonProps {
  isLoading: boolean;
  hasMore: boolean;
  onLoadMore: () => void;
  currentCount: number;
  totalCount: number;
}

/**
 * Load More button for infinite scroll pagination
 */
export default function LoadMoreButton({
  isLoading,
  hasMore,
  onLoadMore,
  currentCount,
  totalCount,
}: LoadMoreButtonProps) {
  if (!hasMore) {
    return (
      <div className="text-center py-4">
        <p className="text-sm text-brand-fg/60">
          Showing all {totalCount} {totalCount === 1 ? 'activity' : 'activities'}
        </p>
      </div>
    );
  }

  return (
    <div className="text-center py-4 space-y-2">
      <Button variant="secondary" onClick={onLoadMore} disabled={isLoading} className="min-w-32">
        {isLoading ? 'Loading...' : 'Load More'}
      </Button>
      <p className="text-xs text-brand-fg/60">
        Showing {currentCount} of {totalCount} {totalCount === 1 ? 'activity' : 'activities'}
      </p>
    </div>
  );
}
