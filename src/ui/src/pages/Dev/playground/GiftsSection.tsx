import { useState } from 'react';
import QueryBoundary from '../../../components/QueryBoundary';
import ErrorMessage from '../../../components/ErrorMessage';
import Button from '../../../components/Button';
import { useGiftTypes, useCreateGift } from '../../../api/gifts/hooks';
import * as giftClient from '../../../api/gifts/client';
import type { GiftReceipt } from '../../../api/types';

/**
 * Section component for gift-related actions.
 */
export default function GiftsSection() {
  const giftTypes = useGiftTypes();
  const createGift = useCreateGift();
  const [giftPetId, setGiftPetId] = useState<string>('');
  const [giftTypeId, setGiftTypeId] = useState<number | ''>('');
  const [giftIdForReceipt, setGiftIdForReceipt] = useState<string>('');
  const [giftReceipt, setGiftReceipt] = useState<GiftReceipt | null>(null);
  const canCreateGift = Boolean(giftPetId && giftTypeId);

  return (
    <section>
      <h2 className="text-lg font-medium mb-2">Gifts</h2>
      <div className="border rounded p-4 space-y-3">
        <div>
          <div className="text-sm font-medium mb-1">Gift Types</div>
          <QueryBoundary loading={giftTypes.isLoading} error={giftTypes.error}>
            <ul className="text-sm list-disc pl-5">
              {(giftTypes.data ?? []).map((t) => (
                <li key={t.id}>
                  {t.name} ({t.cost_in_credits} cr)
                </li>
              ))}
            </ul>
          </QueryBoundary>
        </div>
        <div className="grid sm:grid-cols-2 gap-3">
          <form
            className="space-y-2"
            onSubmit={(e) => {
              e.preventDefault();
              if (canCreateGift) {
                createGift.mutate({ petId: giftPetId, gift_type_id: String(giftTypeId) });
              }
            }}
          >
            <div className="text-sm font-medium">Create Gift</div>
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="pet id"
              value={giftPetId}
              onChange={(e) => setGiftPetId(e.target.value)}
            />
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="gift type id"
              value={giftTypeId}
              onChange={(e) => setGiftTypeId(e.target.value === '' ? '' : Number(e.target.value))}
            />
            {createGift.isError && (
              <ErrorMessage
                message={(createGift.error as { message?: string })?.message || 'Error'}
              />
            )}
            <Button size="sm" isLoading={createGift.isPending} disabled={!canCreateGift}>
              Create Gift
            </Button>
          </form>
          <form
            className="space-y-2"
            onSubmit={async (e) => {
              e.preventDefault();
              setGiftReceipt(null);
              if (giftIdForReceipt) {
                try {
                  const data = await giftClient.exportReceipt(giftIdForReceipt);
                  setGiftReceipt(data as GiftReceipt);
                } catch (err) {
                  setGiftReceipt({
                    error: (err as { message?: string } | undefined)?.message || String(err),
                  });
                }
              }
            }}
          >
            <div className="text-sm font-medium">Export Receipt</div>
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="gift id"
              value={giftIdForReceipt}
              onChange={(e) => setGiftIdForReceipt(e.target.value)}
            />
            <Button size="sm">Export</Button>
          </form>
        </div>
        {giftReceipt ? (
          <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
            {JSON.stringify(giftReceipt, null, 2)}
          </pre>
        ) : null}
      </div>
    </section>
  );
}
