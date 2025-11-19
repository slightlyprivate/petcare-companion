import { FormEvent, useState, useEffect } from 'react';
import Button from '../Button';
import TextInput from '../TextInput';

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
  const [values, setValues] = useState<PetFormValues>(() => ({
    name: '',
    species: '',
    owner_name: '',
    breed: '',
    birth_date: '',
    ...initial,
  }));

  useEffect(() => {
    if (initial) {
      setValues((prev) => ({
        ...prev,
        ...initial,
      }));
    }
  }, [initial]);

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
      <TextInput
        label="Name"
        placeholder="Pet name"
        value={values.name}
        onChange={(e) => update('name', e.target.value)}
        required
      />
      <TextInput
        label="Species"
        placeholder="Dog, Cat, ..."
        value={values.species}
        onChange={(e) => update('species', e.target.value)}
        required
      />
      <TextInput
        label="Owner Name"
        placeholder="Owner name"
        value={values.owner_name}
        onChange={(e) => update('owner_name', e.target.value)}
        required
      />
      <div className="grid gap-3 sm:grid-cols-2">
        <TextInput
          label="Breed (optional)"
          placeholder="Breed"
          value={values.breed || ''}
          onChange={(e) => update('breed', e.target.value)}
        />
        <TextInput
          label="Birth Date (YYYY-MM-DD)"
          placeholder="YYYY-MM-DD"
          value={values.birth_date || ''}
          onChange={(e) => update('birth_date', e.target.value)}
        />
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
