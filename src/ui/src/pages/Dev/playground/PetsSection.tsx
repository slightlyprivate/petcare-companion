import { FormEvent, useMemo, useState } from 'react';
import QueryBoundary from '../../../components/QueryBoundary';
import ErrorMessage from '../../../components/ErrorMessage';
import Button from '../../../components/Button';
import {
  usePublicPets,
  usePublicPet,
  usePets,
  useCreatePet,
  useUpdatePet,
  useDeletePet,
  useRestorePet,
} from '../../../api/pets/hooks';
import * as petsClient from '../../../api/pets/client';
import type { PetReport } from '../../../api/types';

/**
 * Section component for pets-related actions.
 */
export default function PetsSection() {
  const pets = usePublicPets();
  const [petId, setPetId] = useState<string>('');
  const pet = usePublicPet(petId);
  const myPets = usePets();
  const createPet = useCreatePet();
  const updatePet = useUpdatePet();
  const deletePet = useDeletePet();
  const restorePet = useRestorePet();
  const [newPetName, setNewPetName] = useState<string>('');
  const [newPetSpecies, setNewPetSpecies] = useState<string>('');
  const [newPetOwnerName, setNewPetOwnerName] = useState<string>('');
  const [newPetBreed, setNewPetBreed] = useState<string>('');
  const [newPetBirthDate, setNewPetBirthDate] = useState<string>('');
  const [updPetId, setUpdPetId] = useState<string>('');
  const [updName, setUpdName] = useState<string>('');
  const [updSpecies, setUpdSpecies] = useState<string>('');
  const [updOwnerName, setUpdOwnerName] = useState<string>('');
  const [updBreed, setUpdBreed] = useState<string>('');
  const [updBirthDate, setUpdBirthDate] = useState<string>('');
  const [delPetId, setDelPetId] = useState<string>('');
  const [restoreId, setRestoreId] = useState<string>('');

  const [reportPetId, setReportPetId] = useState<string>('');
  const [petReport, setPetReport] = useState<PetReport | null>(null);

  return (
    <section>
      <h2 className="text-lg font-medium mb-2">Pets</h2>
      <div className="border rounded p-4 space-y-3">
        <div>
          <div className="text-sm font-medium mb-1">Public Pets</div>
          <QueryBoundary loading={pets.isLoading} error={pets.error}>
            <ul className="text-sm list-disc pl-5">
              {(pets.data?.data ?? []).map((p) => (
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
                {(myPets.data?.data ?? []).map((p) => (
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
                    setPetReport(data as PetReport);
                  } catch (err) {
                    setPetReport({ error: (err as any)?.message || String(err) });
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
            <Button size="sm" variant="danger" isLoading={deletePet.isPending} disabled={!delPetId}>
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
  );
}
