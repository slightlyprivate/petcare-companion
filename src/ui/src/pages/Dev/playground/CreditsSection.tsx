import QueryBoundary from '../../../components/QueryBoundary';
import ErrorMessage from '../../../components/ErrorMessage';
import Button from '../../../components/Button';
import { useCreditPurchases, usePurchaseCredits } from '../../../api/credits/hooks';
import { useMemo, useState } from 'react';
import Section from '../../../components/Section';
import TextInput from '../../../components/TextInput';

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
    <Section title="Credits">
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
        <TextInput
          placeholder="credit_bundle_id (UUID)"
          value={creditBundleId}
          onChange={(e) => setCreditBundleId(e.target.value)}
        />
        <TextInput
          placeholder="return_url"
          value={creditReturnUrl}
          onChange={(e) => setCreditReturnUrl(e.target.value)}
        />
        {purchaseCredits.isError && (
          <ErrorMessage
            message={(purchaseCredits.error as { message?: string })?.message || 'Error'}
          />
        )}
        <Button size="sm" isLoading={purchaseCredits.isPending} disabled={!canPurchase}>
          Purchase
        </Button>
      </form>
    </Section>
  );
}
