import QueryBoundary from '../../components/QueryBoundary';
import { useCreditPurchases } from '../../api/credits/hooks';
import type { CreditPurchase } from '../../api/types';

/**
 * Purchases page displaying the list of credit purchases for the user.
 */
export default function Purchases() {
  const { data, isLoading, error } = useCreditPurchases();
  const rows = data?.data ?? [];
  return (
    <div>
      <h1 className="text-xl font-semibold mb-3">Credit Purchases</h1>
      <QueryBoundary loading={isLoading} error={error}>
        <ul className="space-y-2">
          {rows.map((r: CreditPurchase) => (
            <li key={r.id} className="border rounded p-3 flex justify-between">
              <div>{r.amount_credits} credits</div>
              <div className="text-sm text-gray-600">{r.status}</div>
            </li>
          ))}
        </ul>
      </QueryBoundary>
    </div>
  );
}
