import { FormEvent, useEffect, useState } from 'react';
import Button from '../Button';

export type PetFormValues = {
  name: string;
  species: string;
  owner_name: string;
  breed?: string;
  birth_date?: string; // YYYY-MM-DD
};

type PetFormProps = {
  initial?: Partial<PetFormValues>;
  onSubmit: (values: PetFormValues) => void | Promise<void>;
  submitLabel?: string;
  isSubmitting?: boolean;
  onCancel?: () => void;
};

export default function PetForm({
  initial,
  onSubmit,
  submitLabel,
  isSubmitting,
  onCancel,
}: PetFormProps) {
  const [values, setValues] = useState<PetFormValues>({
    name: '',
    species: '',
    owner_name: '',
    breed: '',
    birth_date: '',
    ...initial,
  });

  useEffect(() => {
    // Sync when initial changes
    setValues((v) => ({ ...v, ...initial }));
  }, [initial?.name, initial?.species, initial?.owner_name, initial?.breed, initial?.birth_date]);

  const canSubmit = !!values.name && !!values.species && !!values.owner_name;

  function update<K extends keyof PetFormValues>(key: K, val: PetFormValues[K]) {
    setValues((v) => ({ ...v, [key]: val }));
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!canSubmit) return;
    void onSubmit({
      ...values,
      breed: values.breed || undefined,
      birth_date: values.birth_date || undefined,
    });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-3">
      <div className="space-y-1">
        <label className="text-sm">Name</label>
        <input
          className="w-full border rounded px-3 py-2"
          placeholder="Pet name"
          value={values.name}
          onChange={(e) => update('name', e.target.value)}
          required
        />
      </div>
      <div className="space-y-1">
        <label className="text-sm">Species</label>
        <input
          className="w-full border rounded px-3 py-2"
          placeholder="Dog, Cat, ..."
          value={values.species}
          onChange={(e) => update('species', e.target.value)}
          required
        />
      </div>
      <div className="space-y-1">
        <label className="text-sm">Owner Name</label>
        <input
          className="w-full border rounded px-3 py-2"
          placeholder="Owner name"
          value={values.owner_name}
          onChange={(e) => update('owner_name', e.target.value)}
          required
        />
      </div>
      <div className="grid gap-3 sm:grid-cols-2">
        <div className="space-y-1">
          <label className="text-sm">Breed (optional)</label>
          <input
            className="w-full border rounded px-3 py-2"
            placeholder="Breed"
            value={values.breed || ''}
            onChange={(e) => update('breed', e.target.value)}
          />
        </div>
        <div className="space-y-1">
          <label className="text-sm">Birth Date (YYYY-MM-DD)</label>
          <input
            className="w-full border rounded px-3 py-2"
            placeholder="YYYY-MM-DD"
            value={values.birth_date || ''}
            onChange={(e) => update('birth_date', e.target.value)}
          />
        </div>
      </div>

      <div className="flex items-center gap-2 pt-1">
        {onCancel ? (
          <Button type="button" variant="ghost" onClick={onCancel} disabled={isSubmitting}>
            Cancel
          </Button>
        ) : null}
        <Button type="submit" isLoading={!!isSubmitting} disabled={!canSubmit}>
          {submitLabel || 'Save'}
        </Button>
      </div>
    </form>
  );
}
