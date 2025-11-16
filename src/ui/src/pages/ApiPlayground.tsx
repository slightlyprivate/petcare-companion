import { FormEvent, useMemo, useState } from 'react';
import QueryBoundary from '../components/QueryBoundary';
import ErrorMessage from '../components/ErrorMessage';
import Button from '../components/Button';
import { useMe, useLogout, useRequestOtp, useVerifyOtp } from '../api/auth/hooks';
import { usePublicPets, usePublicPet } from '../api/pets/hooks';
import { useGiftTypes, useGiftsByPet, useCreateGift } from '../api/gifts/hooks';
import { useCreditPurchases, usePurchaseCredits } from '../api/credits/hooks';
import {
  useAppointmentsByPet,
  useCreateAppointment,
  useUpdateAppointment,
  useCancelAppointment,
} from '../api/appointments/hooks';

export default function ApiPlayground() {
  // Auth
  const me = useMe();
  const logout = useLogout();
  const requestOtp = useRequestOtp();
  const verifyOtp = useVerifyOtp();
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');

  // Pets
  const pets = usePublicPets();
  const [petId, setPetId] = useState('');
  const pet = usePublicPet(petId);

  // Gifts
  const giftTypes = useGiftTypes();
  const [giftPetId, setGiftPetId] = useState('');
  const giftsByPet = useGiftsByPet(giftPetId);
  const createGift = useCreateGift();
  const [giftTypeId, setGiftTypeId] = useState<number | ''>('');

  // Credits
  const purchases = useCreditPurchases();
  const purchaseCredits = usePurchaseCredits();
  const [creditsAmount, setCreditsAmount] = useState<number | ''>('');

  // Appointments
  const [apptPetId, setApptPetId] = useState('');
  const appts = useAppointmentsByPet(apptPetId);
  const createAppt = useCreateAppointment();
  const updateAppt = useUpdateAppointment();
  const cancelAppt = useCancelAppointment();
  const [apptId, setApptId] = useState('');
  const [apptAt, setApptAt] = useState('');
  const [apptNotes, setApptNotes] = useState('');

  const canCreateGift = useMemo(() => giftPetId && giftTypeId, [giftPetId, giftTypeId]);
  const canPurchase = useMemo(
    () => typeof creditsAmount === 'number' && creditsAmount > 0,
    [creditsAmount],
  );

  function onRequestOtp(e: FormEvent) {
    e.preventDefault();
    if (email) requestOtp.mutate({ email });
  }
  function onVerifyOtp(e: FormEvent) {
    e.preventDefault();
    if (email && code) verifyOtp.mutate({ email, code });
  }

  return (
    <div className="space-y-10">
      <h1 className="text-2xl font-semibold">API Playground</h1>

      {/* Auth */}
      <section>
        <h2 className="text-lg font-medium mb-2">Auth</h2>
        <div className="border rounded p-4 space-y-3">
          <div className="flex items-center justify-between">
            <div className="text-sm">Me:</div>
            <QueryBoundary loading={me.isLoading} error={me.error}>
              <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                {JSON.stringify(me.data, null, 2)}
              </pre>
            </QueryBoundary>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            <form onSubmit={onRequestOtp} className="space-y-2">
              <div className="text-sm font-medium">Request OTP</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
              {requestOtp.isError && (
                <ErrorMessage message={(requestOtp.error as any)?.message || 'Error'} />
              )}
              <Button isLoading={requestOtp.isPending} disabled={!email} size="sm">
                Request
              </Button>
            </form>
            <form onSubmit={onVerifyOtp} className="space-y-2">
              <div className="text-sm font-medium">Verify OTP</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="code"
                value={code}
                onChange={(e) => setCode(e.target.value)}
              />
              {verifyOtp.isError && (
                <ErrorMessage message={(verifyOtp.error as any)?.message || 'Error'} />
              )}
              <Button isLoading={verifyOtp.isPending} disabled={!email || !code} size="sm">
                Verify
              </Button>
            </form>
          </div>
          <div>
            <Button
              variant="secondary"
              size="sm"
              onClick={() => logout.mutate()}
              isLoading={logout.isPending}
            >
              Logout
            </Button>
          </div>
        </div>
      </section>

      {/* Pets */}
      <section>
        <h2 className="text-lg font-medium mb-2">Pets</h2>
        <div className="border rounded p-4 space-y-3">
          <div>
            <div className="text-sm font-medium mb-1">Public Pets</div>
            <QueryBoundary loading={pets.isLoading} error={pets.error}>
              <ul className="text-sm list-disc pl-5">
                {(pets.data?.data ?? []).map((p: any) => (
                  <li key={p.id}>
                    {p.name} — {p.species}
                  </li>
                ))}
              </ul>
            </QueryBoundary>
          </div>
          <div className="grid sm:grid-cols-2 gap-3">
            <div>
              <div className="text-sm font-medium mb-1">Pet by ID</div>
              <input
                className="border rounded px-3 py-1.5 w-full mb-2"
                placeholder="pet id"
                value={petId}
                onChange={(e) => setPetId(e.target.value)}
              />
              <QueryBoundary loading={pet.isLoading} error={pet.error}>
                <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                  {JSON.stringify(pet.data, null, 2)}
                </pre>
              </QueryBoundary>
            </div>
          </div>
        </div>
      </section>

      {/* Gifts */}
      <section>
        <h2 className="text-lg font-medium mb-2">Gifts</h2>
        <div className="border rounded p-4 space-y-3">
          <div>
            <div className="text-sm font-medium mb-1">Gift Types</div>
            <QueryBoundary loading={giftTypes.isLoading} error={giftTypes.error}>
              <ul className="text-sm list-disc pl-5">
                {(giftTypes.data ?? []).map((t: any) => (
                  <li key={t.id}>
                    {t.name} ({t.cost_in_credits} cr)
                  </li>
                ))}
              </ul>
            </QueryBoundary>
          </div>
          <div className="grid sm:grid-cols-2 gap-3">
            <div>
              <div className="text-sm font-medium mb-1">Gifts by Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full mb-2"
                placeholder="pet id"
                value={giftPetId}
                onChange={(e) => setGiftPetId(e.target.value)}
              />
              <QueryBoundary loading={giftsByPet.isLoading} error={giftsByPet.error}>
                <ul className="text-sm list-disc pl-5">
                  {(giftsByPet.data?.data ?? []).map((g: any) => (
                    <li key={g.id}>
                      Gift #{g.id} (type {g.gift_type_id})
                    </li>
                  ))}
                </ul>
              </QueryBoundary>
            </div>
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (canCreateGift) {
                  createGift.mutate({ petId: giftPetId, gift_type_id: Number(giftTypeId) });
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
                onChange={(e) => setGiftTypeId(e.target.value ? Number(e.target.value) : '')}
              />
              {createGift.isError && (
                <ErrorMessage message={(createGift.error as any)?.message || 'Error'} />
              )}
              <Button size="sm" isLoading={createGift.isPending} disabled={!canCreateGift}>
                Create Gift
              </Button>
            </form>
          </div>
        </div>
      </section>

      {/* Credits */}
      <section>
        <h2 className="text-lg font-medium mb-2">Credits</h2>
        <div className="border rounded p-4 space-y-3">
          <div>
            <div className="text-sm font-medium mb-1">Purchases</div>
            <QueryBoundary loading={purchases.isLoading} error={purchases.error}>
              <ul className="text-sm list-disc pl-5">
                {(purchases.data?.data ?? []).map((r: any) => (
                  <li key={r.id}>
                    {r.amount_credits} credits — {r.status}
                  </li>
                ))}
              </ul>
            </QueryBoundary>
          </div>
          <form
            className="space-y-2"
            onSubmit={(e) => {
              e.preventDefault();
              if (canPurchase) purchaseCredits.mutate({ amount_credits: Number(creditsAmount) });
            }}
          >
            <div className="text-sm font-medium">Purchase Credits</div>
            <input
              className="border rounded px-3 py-1.5 w-full"
              placeholder="amount"
              value={creditsAmount}
              onChange={(e) => setCreditsAmount(e.target.value ? Number(e.target.value) : '')}
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

      {/* Appointments */}
      <section>
        <h2 className="text-lg font-medium mb-2">Appointments</h2>
        <div className="border rounded p-4 space-y-3">
          <div className="grid sm:grid-cols-2 gap-3">
            <div>
              <div className="text-sm font-medium mb-1">By Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full mb-2"
                placeholder="pet id"
                value={apptPetId}
                onChange={(e) => setApptPetId(e.target.value)}
              />
              <QueryBoundary loading={appts.isLoading} error={appts.error}>
                <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                  {JSON.stringify(appts.data, null, 2)}
                </pre>
              </QueryBoundary>
            </div>
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (apptPetId && apptAt)
                  createAppt.mutate({
                    petId: apptPetId,
                    at: apptAt,
                    notes: apptNotes || undefined,
                  });
              }}
            >
              <div className="text-sm font-medium">Create</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={apptPetId}
                onChange={(e) => setApptPetId(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="ISO datetime"
                value={apptAt}
                onChange={(e) => setApptAt(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="notes (optional)"
                value={apptNotes}
                onChange={(e) => setApptNotes(e.target.value)}
              />
              {createAppt.isError && (
                <ErrorMessage message={(createAppt.error as any)?.message || 'Error'} />
              )}
              <Button size="sm" isLoading={createAppt.isPending} disabled={!apptPetId || !apptAt}>
                Create
              </Button>
            </form>
          </div>

          <div className="grid sm:grid-cols-2 gap-3">
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (apptPetId && apptId)
                  updateAppt.mutate({
                    petId: apptPetId,
                    apptId,
                    at: apptAt || undefined,
                    notes: apptNotes || undefined,
                  });
              }}
            >
              <div className="text-sm font-medium">Update</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={apptPetId}
                onChange={(e) => setApptPetId(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="appt id"
                value={apptId}
                onChange={(e) => setApptId(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="ISO datetime (optional)"
                value={apptAt}
                onChange={(e) => setApptAt(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="notes (optional)"
                value={apptNotes}
                onChange={(e) => setApptNotes(e.target.value)}
              />
              {updateAppt.isError && (
                <ErrorMessage message={(updateAppt.error as any)?.message || 'Error'} />
              )}
              <Button size="sm" isLoading={updateAppt.isPending} disabled={!apptPetId || !apptId}>
                Update
              </Button>
            </form>
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (apptPetId && apptId) cancelAppt.mutate({ petId: apptPetId, apptId });
              }}
            >
              <div className="text-sm font-medium">Cancel</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={apptPetId}
                onChange={(e) => setApptPetId(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="appt id"
                value={apptId}
                onChange={(e) => setApptId(e.target.value)}
              />
              {cancelAppt.isError && (
                <ErrorMessage message={(cancelAppt.error as any)?.message || 'Error'} />
              )}
              <Button
                size="sm"
                variant="danger"
                isLoading={cancelAppt.isPending}
                disabled={!apptPetId || !apptId}
              >
                Cancel
              </Button>
            </form>
          </div>
        </div>
      </section>
    </div>
  );
}
