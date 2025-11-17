import { FormEvent, useMemo, useState } from 'react';
import QueryBoundary from '../components/QueryBoundary';
import ErrorMessage from '../components/ErrorMessage';
import Button from '../components/Button';
import { useMe, useLogout, useRequestOtp, useVerifyOtp } from '../api/auth/hooks';
import {
  usePublicPets,
  usePublicPet,
  usePets,
  useCreatePet,
  useUpdatePet,
  useDeletePet,
  useRestorePet,
} from '../api/pets/hooks';
import * as petsClient from '../api/pets/client';
import { useGiftTypes, useCreateGift } from '../api/gifts/hooks';
import * as giftClient from '../api/gifts/client';
import { useCreditPurchases, usePurchaseCredits } from '../api/credits/hooks';
import {
  useAppointmentsByPet,
  useCreateAppointment,
  useUpdateAppointment,
  useCancelAppointment,
} from '../api/appointments/hooks';
import {
  useNotificationPreferences,
  useDisableAllNotifications,
  useEnableAllNotifications,
  useExportUserData,
  useDeleteUserData,
} from '../api/user/hooks';

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
  const myPets = usePets();
  const createPet = useCreatePet();
  const updatePet = useUpdatePet();
  const deletePet = useDeletePet();
  const restorePet = useRestorePet();
  const [newPetName, setNewPetName] = useState('');
  const [newPetSpecies, setNewPetSpecies] = useState('');
  const [newPetOwnerName, setNewPetOwnerName] = useState('');
  const [newPetBreed, setNewPetBreed] = useState('');
  const [newPetBirthDate, setNewPetBirthDate] = useState('');
  const [updPetId, setUpdPetId] = useState('');
  const [updName, setUpdName] = useState('');
  const [updSpecies, setUpdSpecies] = useState('');
  const [updOwnerName, setUpdOwnerName] = useState('');
  const [updBreed, setUpdBreed] = useState('');
  const [updBirthDate, setUpdBirthDate] = useState('');
  const [delPetId, setDelPetId] = useState('');
  const [restoreId, setRestoreId] = useState('');

  // Gifts
  const giftTypes = useGiftTypes();
  const [giftPetId, setGiftPetId] = useState('');
  const createGift = useCreateGift();
  const [giftTypeId, setGiftTypeId] = useState<number | ''>('');
  const [giftIdForReceipt, setGiftIdForReceipt] = useState('');
  const [giftReceipt, setGiftReceipt] = useState<any>(null);

  // Credits
  const purchases = useCreditPurchases();
  const purchaseCredits = usePurchaseCredits();
  const [creditBundleId, setCreditBundleId] = useState('');
  const [creditReturnUrl, setCreditReturnUrl] = useState('http://localhost:5173/credits/return');

  // Appointments
  const [apptPetId, setApptPetId] = useState('');
  const appts = useAppointmentsByPet(apptPetId);
  const createAppt = useCreateAppointment();
  const updateAppt = useUpdateAppointment();
  const cancelAppt = useCancelAppointment();
  const [apptId, setApptId] = useState('');
  const [apptTitle, setApptTitle] = useState('');
  const [apptScheduledAt, setApptScheduledAt] = useState('');
  const [apptNotes, setApptNotes] = useState('');

  const canCreateGift = useMemo(() => giftPetId && giftTypeId, [giftPetId, giftTypeId]);
  const canPurchase = useMemo(
    () => !!creditBundleId && !!creditReturnUrl,
    [creditBundleId, creditReturnUrl],
  );
  const [reportPetId, setReportPetId] = useState('');
  const [petReport, setPetReport] = useState<any>(null);

  // User prefs/data
  const prefs = useNotificationPreferences();
  const disableAll = useDisableAllNotifications();
  const enableAll = useEnableAllNotifications();
  const exportData = useExportUserData();
  const deleteData = useDeleteUserData();

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
            <div>
              <div className="text-sm font-medium mb-1">My Pets (auth)</div>
              <QueryBoundary loading={myPets.isLoading} error={myPets.error}>
                <ul className="text-sm list-disc pl-5">
                  {(myPets.data?.data ?? []).map((p: any) => (
                    <li key={p.id}>
                      {p.name} — {p.species}
                    </li>
                  ))}
                </ul>
              </QueryBoundary>
            </div>
            <div>
              <div className="text-sm font-medium mb-1">Public Pet Report</div>
              <form
                className="space-y-2"
                onSubmit={async (e) => {
                  e.preventDefault();
                  setPetReport(null);
                  if (reportPetId) {
                    try {
                      const data = await petsClient.getPublicPetReport(reportPetId);
                      setPetReport(data);
                    } catch (err: any) {
                      setPetReport({ error: err?.message || String(err) });
                    }
                  }
                }}
              >
                <input
                  className="border rounded px-3 py-1.5 w-full"
                  placeholder="pet id"
                  value={reportPetId}
                  onChange={(e) => setReportPetId(e.target.value)}
                />
                <Button size="sm">Fetch Report</Button>
              </form>
              {petReport ? (
                <pre className="mt-2 text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                  {JSON.stringify(petReport, null, 2)}
                </pre>
              ) : null}
            </div>
          </div>
          <div className="grid sm:grid-cols-2 gap-3">
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (newPetName && newPetSpecies && newPetOwnerName)
                  createPet.mutate({
                    name: newPetName,
                    species: newPetSpecies,
                    owner_name: newPetOwnerName,
                    breed: newPetBreed || undefined,
                    birth_date: newPetBirthDate || undefined,
                  });
              }}
            >
              <div className="text-sm font-medium">Create Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="name"
                value={newPetName}
                onChange={(e) => setNewPetName(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="species"
                value={newPetSpecies}
                onChange={(e) => setNewPetSpecies(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="owner_name"
                value={newPetOwnerName}
                onChange={(e) => setNewPetOwnerName(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="breed (optional)"
                value={newPetBreed}
                onChange={(e) => setNewPetBreed(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="birth_date YYYY-MM-DD (optional)"
                value={newPetBirthDate}
                onChange={(e) => setNewPetBirthDate(e.target.value)}
              />
              {createPet.isError && (
                <ErrorMessage message={(createPet.error as any)?.message || 'Error'} />
              )}
              <Button
                size="sm"
                isLoading={createPet.isPending}
                disabled={!newPetName || !newPetSpecies || !newPetOwnerName}
              >
                Create
              </Button>
            </form>
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (updPetId && updName && updSpecies && updOwnerName)
                  updatePet.mutate({
                    id: updPetId,
                    name: updName,
                    species: updSpecies,
                    owner_name: updOwnerName,
                    breed: updBreed || undefined,
                    birth_date: updBirthDate || undefined,
                  });
              }}
            >
              <div className="text-sm font-medium">Update Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={updPetId}
                onChange={(e) => setUpdPetId(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="name (optional)"
                value={updName}
                onChange={(e) => setUpdName(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="species (optional)"
                value={updSpecies}
                onChange={(e) => setUpdSpecies(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="owner_name (required)"
                value={updOwnerName}
                onChange={(e) => setUpdOwnerName(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="breed (optional)"
                value={updBreed}
                onChange={(e) => setUpdBreed(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="birth_date YYYY-MM-DD (optional)"
                value={updBirthDate}
                onChange={(e) => setUpdBirthDate(e.target.value)}
              />
              {updatePet.isError && (
                <ErrorMessage message={(updatePet.error as any)?.message || 'Error'} />
              )}
              <Button
                size="sm"
                isLoading={updatePet.isPending}
                disabled={!updPetId || !updName || !updSpecies || !updOwnerName}
              >
                Update
              </Button>
            </form>
          </div>
          <div className="grid sm:grid-cols-2 gap-3">
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (delPetId) deletePet.mutate(delPetId);
              }}
            >
              <div className="text-sm font-medium">Delete Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={delPetId}
                onChange={(e) => setDelPetId(e.target.value)}
              />
              {deletePet.isError && (
                <ErrorMessage message={(deletePet.error as any)?.message || 'Error'} />
              )}
              <Button
                size="sm"
                variant="danger"
                isLoading={deletePet.isPending}
                disabled={!delPetId}
              >
                Delete
              </Button>
            </form>
            <form
              className="space-y-2"
              onSubmit={(e) => {
                e.preventDefault();
                if (restoreId) restorePet.mutate(restoreId);
              }}
            >
              <div className="text-sm font-medium">Restore Pet</div>
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="pet id"
                value={restoreId}
                onChange={(e) => setRestoreId(e.target.value)}
              />
              {restorePet.isError && (
                <ErrorMessage message={(restorePet.error as any)?.message || 'Error'} />
              )}
              <Button size="sm" isLoading={restorePet.isPending} disabled={!restoreId}>
                Restore
              </Button>
            </form>
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
                <ErrorMessage message={(createGift.error as any)?.message || 'Error'} />
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
                    setGiftReceipt(data);
                  } catch (err: any) {
                    setGiftReceipt({ error: err?.message || String(err) });
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
              <Button size="sm">Fetch Receipt</Button>
              {giftReceipt ? (
                <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                  {JSON.stringify(giftReceipt, null, 2)}
                </pre>
              ) : null}
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
                if (apptPetId && apptTitle && apptScheduledAt)
                  createAppt.mutate({
                    petId: apptPetId,
                    title: apptTitle,
                    scheduled_at: apptScheduledAt,
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
                placeholder="title"
                value={apptTitle}
                onChange={(e) => setApptTitle(e.target.value)}
              />
              <input
                className="border rounded px-3 py-1.5 w-full"
                placeholder="scheduled_at (ISO datetime)"
                value={apptScheduledAt}
                onChange={(e) => setApptScheduledAt(e.target.value)}
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
              <Button
                size="sm"
                isLoading={createAppt.isPending}
                disabled={!apptPetId || !apptTitle || !apptScheduledAt}
              >
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
                    title: apptTitle || undefined,
                    scheduled_at: apptScheduledAt || undefined,
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
                value={apptScheduledAt}
                onChange={(e) => setApptScheduledAt(e.target.value)}
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

      {/* User Preferences & Data */}
      <section>
        <h2 className="text-lg font-medium mb-2">User Preferences & Data</h2>
        <div className="border rounded p-4 space-y-3">
          <div>
            <div className="text-sm font-medium mb-1">Notification Preferences</div>
            <QueryBoundary loading={prefs.isLoading} error={prefs.error}>
              <pre className="text-xs bg-gray-50 p-2 rounded max-w-full overflow-auto">
                {JSON.stringify(prefs.data, null, 2)}
              </pre>
            </QueryBoundary>
          </div>
          <div className="flex gap-2">
            <Button size="sm" onClick={() => enableAll.mutate()} isLoading={enableAll.isPending}>
              Enable All
            </Button>
            <Button
              size="sm"
              variant="secondary"
              onClick={() => disableAll.mutate()}
              isLoading={disableAll.isPending}
            >
              Disable All
            </Button>
          </div>
          <div className="flex gap-2">
            <Button size="sm" onClick={() => exportData.mutate()} isLoading={exportData.isPending}>
              Export My Data
            </Button>
            <Button
              size="sm"
              variant="danger"
              onClick={() => deleteData.mutate()}
              isLoading={deleteData.isPending}
            >
              Delete My Data
            </Button>
          </div>
        </div>
      </section>
    </div>
  );
}
