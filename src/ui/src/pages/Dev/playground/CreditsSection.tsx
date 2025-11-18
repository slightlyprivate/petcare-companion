import QueryBoundary from '../../../components/QueryBoundary';
import ErrorMessage from '../../../components/ErrorMessage';
import Button from '../../../components/Button';
import { useCreditPurchases, usePurchaseCredits } from '../../../api/credits/hooks';
import { useMemo, useState } from 'react';

/**
 * Section component for credits-related actions.
 */
export default function CreditsSection() {
  const purchases = useCreditPurchases();
  const purchaseCredits = usePurchaseCredits();
  const [creditBundleId, setCreditBundleId] = useState<string>('');
  const [creditReturnUrl, setCreditReturnUrl] = useState<string>(
    'http://localhost:5173/credits/return',
  );
  const canPurchase = useMemo(
    () => !!creditBundleId && !!creditReturnUrl,
    [creditBundleId, creditReturnUrl],
  );

  return (
    <section>
      <h2 className="text-lg font-medium mb-2">Credits</h2>
      <div className="border rounded p-4 space-y-3">
        <div>
          <div className="text-sm font-medium mb-1">Purchases</div>
          <QueryBoundary loading={purchases.isLoading} error={purchases.error}>
            <ul className="text-sm list-disc pl-5">
              {(purchases.data?.data ?? []).map((r) => (
                <li key={r.id}>
                  #{r.id} — {r.amount_credits} credits — {r.status}
                </li>
              ))}
            </ul>
          </QueryBoundary>
        </div>
        <form
          className="space-y-2"
          onSubmit={(e) => {
            e.preventDefault();
            if (canPurchase)
              purchaseCredits.mutate({
                credit_bundle_id: creditBundleId,
                return_url: creditReturnUrl,
              });
          }}
        >
          <div className="text-sm font-medium">Purchase Credits</div>
          <input
            className="border rounded px-3 py-1.5 w-full"
            placeholder="credit_bundle_id (UUID)"
            value={creditBundleId}
            onChange={(e) => setCreditBundleId(e.target.value)}
          />
          <input
            className="border rounded px-3 py-1.5 w-full"
            placeholder="return_url"
            value={creditReturnUrl}
            onChange={(e) => setCreditReturnUrl(e.target.value)}
          />
          {purchaseCredits.isError && (
            <ErrorMessage message={(purchaseCredits.error as any)?.message || 'Error'} />
          )}
          <Button size="sm" isLoading={purchaseCredits.isPending} disabled={!canPurchase}>
            Purchase
          </Button>
        </form>
      </div>
    </section>
  );
}
